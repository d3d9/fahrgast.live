<?php

namespace App\Http\Controllers\Backend\Export;

use App\Enum\ExportableColumn;
use App\Enum\StatusTagKey;
use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\Format\JsonExportController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\TravelChain;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ExportController extends Controller
{

    /**
     * @throws DataOverflowException If too many results are given.
     */
    public static function getExportableStatuses(User $user, Carbon $timestampFrom, Carbon $timestampTo): Collection {
        $statuses = Status::with([
                                     //'checkin.trip.stopovers', TODO: This eager load is doing weird things. Some Trips aren't loaded and this throws some http 500. Loading this manually is working.
                                     'checkin.originStation',
                                     'checkin.destinationStation',
                                 ])
                          ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                          ->where('statuses.user_id', $user->id)
                          ->where('train_checkins.departure', '>=', $timestampFrom->startOfDay())
                          ->where('train_checkins.departure', '<=', $timestampTo->endOfDay())
                          ->select(['statuses.*'])
                          ->limit(2001)
                          ->get();
        // A user should only be able to export 2000 statuses at once to avoid memory
        // overflows. Thus, if the database returns 2001 entries (which is the limit),
        // there are `>2000` statuses in this time frame and the user must choose a
        // smaller time frame.
        if ($statuses->count() === 2001) {
            throw new DataOverflowException();
        }
        return $statuses;
    }

    /**
     * @throws DataOverflowException
     */
    public static function generateExport(
        Carbon $from,
        Carbon $until,
        array $columns,
        string $filetype
    ): HttpResponse|StreamedResponse {
        $data = self::getExportData($from, $until, $columns);

        if ($filetype === 'pdf') {
            return self::exportPdf(
                from:    $from,
                until:   $until,
                columns: $columns,
                data:    $data,
            );
        }

        if ($filetype === 'csv_human' || $filetype === 'csv_machine') {
            return self::exportCsv(
                from:                  $from,
                until:                 $until,
                columns:               $columns,
                data:                  $data,
                humanReadableHeadings: $filetype === 'csv_human',
            );
        }

        throw new InvalidArgumentException('unsupported filetype');
    }

    private static function getExportMapping(Status $status, ExportableColumn $column) {

        switch ($column) {
            case ExportableColumn::STATUS_ID:
                return $status->id;
            case ExportableColumn::JOURNEY_TYPE:
                return $status->checkin->trip->category->value;
            case ExportableColumn::LINE_NAME:
                return $status->checkin->trip->linename;
            case ExportableColumn::JOURNEY_NUMBER:
                return $status->checkin->trip->journey_number;
            case ExportableColumn::ORIGIN_NAME:
                return $status->checkin->originStation->name;
            case ExportableColumn::ORIGIN_COORDINATES:
                return $status->checkin->originStation->latitude
                       . ',' . $status->checkin->originStation->longitude;
            case ExportableColumn::DEPARTURE_PLANNED:
                return $status->checkin->originStopover?->departure_planned?->toIso8601String();
            case ExportableColumn::DEPARTURE_REAL:
                return $status->checkin->originStopover?->departure?->toIso8601String();
            case ExportableColumn::DESTINATION_NAME:
                return $status->checkin->destinationStation->name;
            case ExportableColumn::DESTINATION_COORDINATES:
                return $status->checkin->destinationStation->latitude
                       . ',' . $status->checkin->destinationStation->longitude;
            case ExportableColumn::ARRIVAL_PLANNED:
                return $status->checkin->destinationStopover?->arrival_planned?->toIso8601String();
            case ExportableColumn::ARRIVAL_REAL:
                return $status->checkin->destinationStopover?->arrival?->toIso8601String();
            case ExportableColumn::DURATION:
                return $status->checkin->duration;
            case ExportableColumn::DISTANCE:
                return $status->checkin->distance;
            case ExportableColumn::POINTS:
                return $status->checkin->points;
            case ExportableColumn::BODY:
                return $status->body;
            case ExportableColumn::TRAVEL_TYPE:
                return $status->business->name;
            case ExportableColumn::OPERATOR:
                return $status->checkin->trip?->operator?->name;
            case ExportableColumn::STATUS_TAGS:
                $tags = [];
                foreach ($status->tags as $tag) {
                    $tags[$tag->key] = $tag->value;
                }
                return $tags;
            default:
                throw new InvalidArgumentException('unsupported column');
        }
    }

    /**
     * @throws DataOverflowException
     */
    public static function getExportData(Carbon $timestampFrom, Carbon $timestampTo, array &$columns): array {
        $statuses = self::getExportableStatuses(auth()->user(), $timestampFrom, $timestampTo);
        $data     = [];
        $tagKeys  = [];
        $statusTags = [];
        foreach ($statuses as $key => $status) {
            $row  = [];
            $tags = [];

            foreach ($columns as $column) {
                if (!($column instanceof ExportableColumn)) {
                    continue;
                }
                if ($column === ExportableColumn::STATUS_TAGS) {
                    $tags = self::getExportMapping($status, $column);
                    foreach ($tags as $tag => $value) {
                        if (!in_array($tag, $tagKeys, true)) {
                            $tagKeys[] = $tag;
                        }
                    }
                    $statusTags[$key] = $tags;

                    continue;
                }
                $row[$column->value] = self::getExportMapping($status, $column);
            }

            $data[$key] = $row;
        }

        foreach ($statusTags as $key => $tags) {
            foreach ($tagKeys as $tagKey) {
                $data[$key][$tagKey] = $tags[$tagKey] ?? null;
            }
        }

        array_push($columns, ...$tagKeys);

        if (($key = array_search(ExportableColumn::STATUS_TAGS, $columns, true)) !== false) {
            unset($columns[$key]);
        }

        return $data;
    }

    /**
     * FGL
     * admin only
     */
    public static function generateFullStatusExport() {
        $statuses = Status::with([
                                     //'checkin.trip.stopovers', TODO: This eager load is doing weird things. Some Trips aren't loaded and this throws some http 500. Loading this manually is working.
                                     'checkin.originStation',
                                     'checkin.destinationStation',
                                 ])
                          ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                          ->select(['statuses.*'])
                          ->orderBy('chain_id', 'ASC')
                          ->orderBy('id', 'ASC')
                          ->get();
        $data = [];
        foreach ($statuses as $key => $status) {
            $dd = $status->checkin->displayDeparture;
            $da = $status->checkin->displayArrival;
            $dp = $status->checkin->originStopover?->departure_planned;
            $ap = $status->checkin->destinationStopover?->arrival_planned;
            $duration_planned = $dp->diffInMinutes($ap, false); // buggy daher nicht attribut sondern berechnet
            $delay = isset($da->original) ? $da->original->diffInMinutes($da->time, false) : 0;
            $departure_delay = isset($dd->original) ? $dd->original->diffInMinutes($dd->time, false) : 0;
            $row = [
                'status_id' => $status->id,
                'user_id' => $status->user->id,
                'chain_id' => $status->travelchain?->id,
                'planned' => $status->planned ? 'true' : 'false',
                'taken' => $status->taken ? 'true' : 'false',
                'not_taken_reason' => $status->not_taken_reason?->getReason(),
                'category' => $status->checkin->trip->category->value,
                'linename' => $status->checkin->trip->linename,
                'journey_number' => $status->checkin->trip->journey_number,
                'direction' => $status->checkin->trip?->destinationStation?->name,
                'operator' => $status->checkin->trip?->operator?->name,
                'origin' => $status->checkin->originStation->name,
                'origin_coords' => $status->checkin->originStation->latitude . ',' . $status->checkin->originStation->longitude,
                'departure' => $dd->time,
                'departure_type' => strtolower($dd->type->name),
                'departure_planned' => $dp?->toIso8601String(),
                //'departure_real' => $status->checkin->originStopover?->departure_real?->toIso8601String(),
                //'departure_manual' => $status->checkin->manual_departure?->toIso8601String(),
                'destination' => $status->checkin->destinationStation->name,
                'destination_coords' => $status->checkin->destinationStation->latitude . ',' . $status->checkin->destinationStation->longitude,
                'arrival' => $da->time,
                'arrival_type' => strtolower($da->type->name),
                'arrival_planned' => $ap?->toIso8601String(),
                //'arrival_real' => $status->checkin->destinationStopover?->arrival_real?->toIso8601String(),
                //'arrival_manual' => $status->checkin->manual_arrival?->toIso8601String(),
                'delay' => $delay,
                'departure_delay' => $departure_delay,
                'Δdelay_arr_dep' => $delay - $departure_delay, // Wie viele Minuten Verspätung haben sich angesammelt / abgebaut während der Fahrt
                'duration_planned' => $duration_planned,
                'duration' => $status->checkin->duration,
                'Δduration' => $status->checkin->duration - $duration_planned,
                '%Δduration' => ($status->checkin->duration / $duration_planned) - 1,
                'distance' => $status->checkin->distance,
                'travel_type' => $status->travelchain?->business?->title(), // von chain nur. + ?
                'body' => $status->body,
                'created' => $status->created_at->toIso8601String(),
                'Δcreated_departure' => $dd->time->diffInMinutes($status->created_at, false),
            ];

            $data[$key] = $row;
        }

        return self::exportCsv(
            from:                  new Carbon(),
            until:                 new Carbon(),
            columns:               empty($data) ? [] : array_keys($data[0]),
            data:                  $data,
            humanReadableHeadings: false,
        );
    }

    private static function _stationDistance($plan, $real) {
        if (isset($plan) && isset($real)) {
            if ($plan->is($real)) {
                return 0;
            } else {
                // https://stackoverflow.com/a/10054282
                $latFrom = deg2rad($plan->latitude);
                $lonFrom = deg2rad($plan->longitude);
                $latTo = deg2rad($real->latitude);
                $lonTo = deg2rad($real->longitude);

                $latDelta = $latTo - $latFrom;
                $lonDelta = $lonTo - $lonFrom;

                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

                return round($angle * 6371000);
            }
        } else return NULL;
    }

    private static function _nm($a, $b) {
        if (is_null($a) && is_null($b)) return NULL;
        return $a - $b;
    }

    /**
     * FGL
     * admin only
     */
    public static function generateFullTravelchainExport() {
        $chains = TravelChain::with(['statuses'])->get();
        $data = [];
        foreach ($chains as $key => $chain) {
            $plannedStatuses = $chain->plannedStatuses;
            $takenStatuses = $chain->takenStatuses;

            // kopiert vom controller
            $planOrigin = $plannedStatuses->firstStopover()?->station;
            $realOrigin = $takenStatuses->firstStopover()?->station;
            $delta_origin_m = self::_stationDistance($planOrigin, $realOrigin);
            $planDestination = $plannedStatuses->lastStopover()?->station;
            $realDestination = $takenStatuses->lastStopover()?->station;
            $delta_destination_m = self::_stationDistance($planDestination, $realDestination);

            // kopiert von view
            $plannedDep = $plannedStatuses->depPlanned();
            $plannedArr = $plannedStatuses->arrPlanned();
            $plannedDur = $plannedDep?->diffInMinutes($plannedArr, false);

            $takenDep = $takenStatuses->depReal();
            $takenArr = $takenStatuses->arrReal();
            $takenDur = $takenDep?->diffInMinutes($takenArr, false);

            $plannedIvt = $plannedStatuses->checkinPlannedDurationMins();
            $actualIvt = $takenStatuses->checkinDurationMins();

            $plannedCategoriesDurations = array();
            $plannedLongestChange = NULL;
            $takenCategoriesDurations = array();
            $takenStatusHighestDelay = NULL;
            $takenStatusHighestDepartureDelay = NULL;
            $takenStatusHighestDeltaArrDep = NULL;
            $takenLongestChange = NULL;

            $ap = NULL;
            foreach($plannedStatuses as $pStatus) {
                $category = $pStatus->checkin?->trip?->category?->value;
                $dp = $pStatus->checkin->originStopover?->departure_planned;

                if (isset($ap)) {
                    // planned
                    $changeTime = $ap->diffInMinutes($dp, false);
                    if (empty($plannedLongestChange) || $changeTime > $plannedLongestChange) {
                        $plannedLongestChange = $changeTime;
                    }
                }

                $ap = $pStatus->checkin->destinationStopover?->arrival_planned;
                $duration_planned = $dp->diffInMinutes($ap, false); // buggy daher nicht attribut sondern berechnet
                if (!empty($category)) {
                    if (!array_key_exists($category, $plannedCategoriesDurations)) {
                        $plannedCategoriesDurations[$category] = $duration_planned;
                    } else $plannedCategoriesDurations[$category] += $duration_planned;
                }
            }

            $da = NULL;
            foreach($takenStatuses as $tStatus) {
                $category = $tStatus->checkin?->trip?->category?->value;

                $dd = $tStatus->checkin->displayDeparture;

                if (isset($da)) {
                    // taken (actual)
                    $changeTime = $da->time->diffInMinutes($dd->time, false);
                    if (empty($takenLongestChange) || $changeTime > $takenLongestChange) {
                        $takenLongestChange = $changeTime;
                    }
                }

                $da = $tStatus->checkin->displayArrival;

                $delay = isset($da->original) ? $da->original->diffInMinutes($da->time, false) : 0;
                if (empty($takenStatusHighestDelay) || $delay > $takenStatusHighestDelay) {
                    $takenStatusHighestDelay = $delay;
                }
                $departure_delay = isset($dd->original) ? $dd->original->diffInMinutes($dd->time, false) : 0;
                if (empty($takenStatusHighestDepartureDelay) || $departure_delay > $takenStatusHighestDepartureDelay) {
                    $takenStatusHighestDepartureDelay = $departure_delay;
                }
                $deltadelay = $delay - $departure_delay;
                if (empty($takenStatusHighestDeltaArrDep) || $deltadelay > $takenStatusHighestDeltaArrDep) {
                    $takenStatusHighestDeltaArrDep = $deltadelay;
                }

                if (!empty($category)) {
                    if (!array_key_exists($category, $takenCategoriesDurations)) {
                        $takenCategoriesDurations[$category] = $tStatus->checkin->duration;
                    } else $takenCategoriesDurations[$category] += $tStatus->checkin->duration;
                }
            }

            $row = [
                'chain_id' => $chain->id,
                'user_id' => $chain->user->id,
                'title' => $chain->title,
                'travel_type' => $chain->business?->title(),
                'startDate' => $plannedStatuses->firstStopover()?->departure_planned?->isoFormat('l'),
                'weekday' => $plannedStatuses->firstStopover()?->departure_planned?->isoFormat('dd'),
                'reliability_importance' => $chain->reliability_importance?->value,
                'planned_for_reliability' => $chain->planned_for_reliability?->value,
                'finished' => $chain->finished?->value,
                'felt_punctual' => $chain->felt_punctual?->value,
                'felt_stressed' => $chain->felt_stressed?->value,
                // Einzelstatuses betrachtet
                'planned_statuses' => implode(';', $plannedStatuses->pluck('id')->all()),
                'taken_statuses' => implode(';', $takenStatuses->pluck('id')->all()),
                '≠statuses' => $plannedStatuses->diff($takenStatuses)->isNotEmpty() ? 'true' : 'false',
                'planned_status_count' => $plannedStatuses->count(),
                'taken_status_count' => $takenStatuses->count(),
                'Δstatus_count' => $takenStatuses->count() - $plannedStatuses->count(),
                'planned_changes' => $plannedStatuses->count() - 1,
                'taken_changes' => $takenStatuses->count() - 1,
                // vgl. Δstatus_count: 'Δchanges' => ($takenStatuses->count() - 1) - ($plannedStatuses->count() - 1),
                'planned_status_linenames' => implode(';', $plannedStatuses->pluck('checkin.trip.linename')->all()),
                'taken_status_linenames' => implode(';', $takenStatuses->pluck('checkin.trip.linename')->all()),
                '≠status_linenames' => NULL, // post
                'planned_status_categories' => implode(';', $plannedStatuses->pluck('checkin.trip.category.value')->all()),
                'taken_status_categories' => implode(';', $takenStatuses->pluck('checkin.trip.category.value')->all()),
                '≠status_categories' => NULL, // post
                'planned_status_categories_first' => $plannedStatuses->first()?->checkin?->trip?->category?->value,
                'taken_status_categories_first' => $takenStatuses->first()?->checkin?->trip?->category?->value,
                '≠status_categories_first' => NULL, // post
                'planned_status_categories_longest' => implode(';', array_keys($plannedCategoriesDurations, max($plannedCategoriesDurations))),
                'taken_status_categories_longest' => implode(';', array_keys($takenCategoriesDurations, max($takenCategoriesDurations))),
                '≠status_categories_longest' => NULL, // post
                'planned_status_not_taken_count' => $plannedStatuses->where('taken', false)->count(),
                'not_taken_reasons' => implode(';', $plannedStatuses->where('taken', false)->pluck('not_taken_reason')->map(function($ntr) { return $ntr?->getReason(); })->all()),
                // verworfen weil erstmal zu detailliert
                // 'planned_status_longest_duration' => NULL,
                // 'taken_status_longest_duration' => NULL,
                // 'Δstatus_longest_duration' => NULL,
                // '%Δstatus_longest_duration' => NULL,
                'taken_status_highest_delay' => $takenStatusHighestDelay,
                'taken_status_highest_departure_delay' => $takenStatusHighestDepartureDelay,
                'taken_status_highest_Δ_arr_dep' => $takenStatusHighestDeltaArrDep,
                'planned_longest_change' => $plannedLongestChange,
                'taken_longest_change' => $takenLongestChange,
                'Δlongest_change' => self::_nm($takenLongestChange, $plannedLongestChange),
                // Reisekette an sich betrachtet
                'planned_departure' => $plannedDep->toIso8601String(),
                'planned_departure_time' => userTime($plannedDep),
                'planned_origin' => $plannedStatuses->firstStopover()?->station?->name,
                'planned_origin_coords' => $plannedStatuses->firstStopover()?->station?->latitude . ',' . $plannedStatuses->firstStopover()?->station?->longitude,
                'actual_departure' => $takenDep->toIso8601String(),
                'actual_departure_time' => userTime($takenDep),
                'actual_origin' => $takenStatuses->firstStopover()?->station?->name,
                'actual_origin_coords' => $takenStatuses->firstStopover()?->station?->latitude . ',' . $takenStatuses->firstStopover()?->station?->longitude,
                'Δdeparture' => $plannedDep->diffInMinutes($takenDep, false),
                // '≠origin' => NULL, // verworfen // abgedeckt über folgendes:
                'Δorigin_coords_m' => $delta_origin_m,
                'planned_arrival' => $plannedArr->toIso8601String(),
                'planned_arrival_time' => userTime($plannedArr),
                'planned_destination' => $plannedStatuses->lastStopover()?->station?->name,
                'planned_destination_coords' => $plannedStatuses->lastStopover()?->station?->latitude . ',' . $plannedStatuses->lastStopover()?->station?->longitude,
                'actual_arrival' => $takenArr->toIso8601String(),
                'actual_arrival_time' => userTime($takenArr),
                'actual_destination' => $takenStatuses->lastStopover()?->station?->name,
                'actual_destination_coords' => $takenStatuses->lastStopover()?->station?->latitude . ',' . $takenStatuses->lastStopover()?->station?->longitude,
                'Δarrival' => $plannedArr->diffInMinutes($takenArr, false),
                // '≠destination' => NULL, // verworfen // abgedeckt über folgendes:
                'Δdestination_coords_m' => $delta_destination_m,
                'planned_duration' => $plannedDur,
                'actual_duration' => $takenDur,
                'Δduration' => $takenDur - $plannedDur,
                '%Δduration' => ($takenDur / $plannedDur) - 1,
                'planned_ivt' => $plannedIvt,
                'actual_ivt' => $actualIvt,
                'Δivt' => $actualIvt - $plannedIvt,
                '%Δivt' => ($actualIvt / $plannedIvt) - 1,
                'planned_distance' => round($plannedStatuses->distance() / 1000),
                'taken_distance' => round($takenStatuses->distance() / 1000),
                'Δdistance' => round(($takenStatuses->distance() - $plannedStatuses->distance()) / 1000),
                '%Δdistance' => ($takenStatuses->distance() / $plannedStatuses->distance()) - 1,
                'body' => $chain->body,
            ];
            $row['≠status_linenames'] = ($row['planned_status_linenames'] != $row['taken_status_linenames']) ? 'true' : 'false';
            $row['≠status_categories'] = ($row['planned_status_categories'] != $row['taken_status_categories']) ? 'true' : 'false';
            $row['≠status_categories_first'] = ($row['planned_status_categories_first'] != $row['taken_status_categories_first']) ? 'true' : 'false';
            $row['≠status_categories_longest'] = ($row['planned_status_categories_longest'] != $row['taken_status_categories_longest']) ? 'true' : 'false';

            $data[$key] = $row;
        }

        return self::exportCsv(
            from:                  new Carbon(),
            until:                 new Carbon(),
            columns:               empty($data) ? [] : array_keys($data[0]),
            data:                  $data,
            humanReadableHeadings: false,
        );
    }

    /**
     * @throws DataOverflowException
     */
    public static function exportJson(Carbon $begin, Carbon $end): JsonResponse {
        $headers    = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/json',
            'Content-Disposition' => sprintf(
                'attachment; filename="Traewelling_export_%s_to_%s.json"',
                $begin->format('Y-m-d'),
                $end->format('Y-m-d')
            ),
            'Expires'             => '0',
            'Pragma'              => 'public',
        ];
        $exportData = JsonExportController::generateExport(auth()->user(), $begin, $end);
        return Response::json(data: $exportData, headers: $headers);
    }

    private static function exportPdf(Carbon $from, Carbon $until, array $columns, array $data): HttpResponse {
        return Pdf::loadView('pdf.export-template', [
            'begin'   => $from,
            'end'     => $until,
            'columns' => $columns,
            'data'    => $data,
        ])
                  ->setPaper('a4', 'landscape')
                  ->download(
                      sprintf(
                          'Traewelling_export_%s_to_%s.pdf',
                          $from->format('Y-m-d'),
                          $until->format('Y-m-d')
                      )
                  );
    }

    private static function exportCsv(
        Carbon $from,
        Carbon $until,
        array $columns,
        array $data,
        bool $humanReadableHeadings = false
    ): StreamedResponse {
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => sprintf(
                'attachment; filename="Traewelling_export_%s_to_%s.csv"',
                $from->format('Y-m-d'),
                $until->format('Y-m-d')
            ),
            'Expires'             => '0',
            'Pragma'              => 'public',
        ];

        $fileStream = static function () use ($humanReadableHeadings, $columns, $data) {
            $csv           = fopen('php://output', 'w');
            $stringColumns = [];
            foreach ($columns as $column) {
                if ($humanReadableHeadings) {
                    $stringColumns[] = self::getColumnTitle($column);
                    continue;
                }
                $stringColumns[] = $column->value ?? $column;
            }
            fputcsv(
                stream: $csv,
                fields: $stringColumns,
            );
            foreach ($data as $row) {
                fputcsv($csv, $row);
            }
            fclose($csv);
        };

        return Response::stream($fileStream, 200, $headers);
    }

    public static function getColumnTitle(ExportableColumn|String $column): string {
        if ($column instanceof ExportableColumn) {
            return $column->title();
        }

        $key = StatusTagKey::tryFrom($column);
        return $key?->title() ?? $column;
    }
}
