<?php

namespace App\Http\Controllers;

use App\Dto\CheckinSuccess;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Events\StatusDeleteEvent;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\Transport\HomeController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\Event;
use App\Models\TravelChain;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Throwable;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendTransportController extends Controller
{
    public function TrainAutocomplete(string $station): JsonResponse {
        try {
            $TrainAutocompleteResponse = TransportBackend::getTrainStationAutocomplete($station);
            return response()->json($TrainAutocompleteResponse);
        } catch (HafasException $e) {
            abort(503, $e->getMessage());
        }
    }

    public function LocationAutocomplete(string $query): JsonResponse {
        try {
            $LocationAutocompleteResponse = TransportBackend::getLocationAutocomplete($query);
            return response()->json($LocationAutocompleteResponse);
        } catch (HafasException $e) {
            abort(503, $e->getMessage());
        }
    }

    public function TrainStationboard(Request $request): Renderable|RedirectResponse {
        $validated = $request->validate([
                                            'station'    => ['required_without:ibnr'],
                                            'ibnr'       => ['required_without:station', 'numeric'],
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', new Enum(TravelType::class)]
                                        ]);

        $when = isset($validated['when'])
            ? Carbon::parse($validated['when'], auth()->user()->timezone ?? config('app.timezone'))
            : Carbon::now(auth()->user()->timezone ?? config('app.timezone'))->subMinutes(5);

        try {
            //Per default: Use the given station query for lookup
            $searchQuery = $validated['station'] ?? $validated['ibnr'];

            //If a station_id is given (=user is already on a stationboard) check if the user changed the query.
            //If so: Use the given station string. Otherwise, use the station_id for lookup.
            //This is to prevent that HAFAS fuzzy search return other stations (e.g. "Bern, Hauptbahnhof", Issue 1082)
            if (isset($validated['ibnr']) && $searchQuery !== $validated['ibnr']) {
                $station = HafasController::getStation($validated['ibnr']);
                if ($station->name === $validated['station']) {
                    $searchQuery = $station->ibnr;
                }
            }
            $stationboardResponse = TransportBackend::getDepartures(
                stationQuery: $searchQuery,
                when:         $when,
                travelType:   TravelType::tryFrom($validated['travelType'] ?? null),
                localtime:    true
            );
            return view('stationboard', [
                                          'station'    => $stationboardResponse['station'],
                                          'departures' => $stationboardResponse['departures'],
                                          'times'      => $stationboardResponse['times'],
                                          'latest'     => TransportController::getLatestArrivals(Auth::user())
                                      ]
            );
        } catch (HafasException $exception) {
            report($exception);
            return back()->with('error', __('messages.exception.generalHafas'));
        } catch (ModelNotFoundException) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }
    }

    public function TrainJourneyPlanner(Request $request): Renderable|RedirectResponse {
        $validated = $request->validate([
                                            'earlierRef' => ['nullable', 'string', 'prohibits:laterRef'],
                                            'laterRef' => ['nullable', 'string', 'prohibits:earlierRef'],
                                            'location_origin'       => ['required'],
                                            'location_origin_value' => ['required'],
                                            'location_destination'       => ['required'],
                                            'location_destination_value' => ['required'],
                                            'when'       => ['nullable', 'date'],
                                            'arr' => ['required', 'boolean'],
                                            'travelType' => ['array'],
                                            'travelType.*' => [new Enum(TravelType::class)],
                                            'transferTime' => ['nullable', 'integer', 'min:0'],
                                            'walkingSpeed' => ['required', 'string', 'in:slow,normal,fast']
                                        ]);

        $when = isset($validated['when'])
            ? Carbon::parse($validated['when'], auth()->user()->timezone ?? config('app.timezone'))
            : Carbon::now(auth()->user()->timezone ?? config('app.timezone'))->subMinutes(5);

        try {
            $journeysResponse = TransportBackend::getJourneys(
                origin: $validated['location_origin_value'],
                destination: $validated['location_destination_value'],
                when: $when,
                arr: $validated['arr'],
                earlierRef: $validated['earlierRef'] ?? null,
                laterRef: $validated['laterRef'] ?? null,
                travelType: isset($validated['travelType']) ? array_map(function($tts) { return TravelType::tryFrom($tts) ?? null; }, $validated['travelType']) : array(),
                transferTime: $validated['transferTime'] ?? 0,
                walkingSpeed: $validated['walkingSpeed']
            );

            $hafasJourneys = $journeysResponse->journeys;
            $journeys = array();

            foreach($hafasJourneys as $journey) {
                $request->session()->put('journey_'.$journey->refreshToken, $journey);

                // wie in controller weiter unten
                $transportLegs = array_filter($journey->legs, function($l) { return !isset($l->walking) || !$l->walking; });

                $journeyInfo = ['transportLegs' => $transportLegs ];
                $hasTransport = !empty($transportLegs);
                $journeyInfo['hasTransport'] = $hasTransport;
                $journeyInfo['lastTransport'] = $hasTransport ? end($transportLegs) : null;
                $journeyInfo['firstTransport'] = $hasTransport ? reset($transportLegs) : null;
                $journeyInfo['changes'] = $hasTransport ? (count($transportLegs) - 1) : 0;
                $journeys[$journey->refreshToken] = ['journey' => $journey, 'info' => $journeyInfo];
            }

            return view('journey-planner', [
                                          'journeys' => $journeys,
                                          'earlierRef' => $journeysResponse->earlierRef,
                                          'laterRef' => $journeysResponse->laterRef,
                                          // 'latest'     => TransportController::getLatestArrivals(Auth::user())
                                      ]
            );
        } catch (HafasException $exception) {
            report($exception);
            // return redirect->back()->with('error', __('messages.exception.generalHafas'));
            return view('journey-planner', ['error' => "Es konnten keine Ergebnisse aus der Fahrplanauskunft abgerufen werden. Bitte versuchen Sie es erneut oder passen Sie die Suchkriterien an."]);
        } catch (ModelNotFoundException) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }
    }

    public function TrainJourneyCheckin(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'token'            => ['required'],
                                        ]);

        $journey = $request->session()->get("journey_".$validated['token']);
        if (!isset($journey)) {
            return redirect()->back()->with('error', 'Route unbekannt, bitte erneut versuchen');
        }

        $transportLegs = array_filter($journey->legs, function($l) { return !isset($l->walking) || !$l->walking; }); // wie in view und oben
        if (empty($transportLegs)) {
            return redirect()->back()->with('error', 'Route enthält keine Fahrten im ÖPNV');
        }

        $user = Auth::user();

        $travelChain = TravelChainController::createTravelChain($user, null, null, null);

        try {
            try {
                $statuses = [];

                $firstLeg = null;
                $journeyOrigin = null;
                $lastLeg = null;
                $journeyDestination = null;

                foreach($transportLegs as $leg) {
                    $trip = HafasController::getHafasTrip($leg->tripId, $leg->line->name ?? $leg->line->fahrtNr);
                    // ^ this upserts the stations required now:
                    $origin = Station::where('ibnr', $leg->origin->id)->first();
                    $destination = Station::where('ibnr', $leg->destination->id)->first();

                    if (!isset($firstLeg)) {
                        $firstLeg = $leg;
                        $journeyOrigin = $origin;
                    }
                    $lastLeg = $leg;
                    $journeyDestination = $destination;

                    $backendResponse = TrainCheckinController::checkin(
                        user:         $user,
                        trip:         $trip,
                        origin:       $origin,
                        departure:    Carbon::parse($leg->plannedDeparture),
                        destination:  $destination,
                        arrival:      Carbon::parse($leg->plannedArrival),
                        travelChain:  $travelChain,
                        planned:      true,
                        travelReason: Business::PRIVATE,
                        visibility:   StatusVisibility::PRIVATE
                    );

                    $status = $backendResponse['status'];
                    $statuses[] = $status;
                    $checkin = $status->checkin;
                }

                $_nach = " nach ";
                $maxlength = 255 - strlen($_nach);
                $title = userTime($firstLeg->plannedDeparture, __('datetime-format-short')) . ' von ';
                $maxlength -= mb_strlen($title);
                $_from = $journeyOrigin->name;
                $_from_len = mb_strlen($_from);
                $_to = $journeyDestination->name;
                $_to_len = mb_strlen($_to);
                if (($_from_len + $_to_len) < $maxlength) {
                    $title .= $_from . $_nach . $_to;
                } else if ($_from_len < $maxlength) {
                    $title .= $_from;
                } else {
                    $title .= substr($_from, 0, $maxlength);
                }
                $travelChain->title = $title;
                $travelChain->save();

                return redirect()->route('travelchain', ['id' => $travelChain->id]);
            } catch (Throwable $exception) {
                foreach($statuses as $status) {
                    $status->delete();
                    StatusDeleteEvent::dispatch($status);
                }
                $travelChain->delete();
                throw $exception;
            }
        } catch (CheckInCollisionException $exception) {
            return redirect()
                ->route('dashboard')
                ->with('checkin-collision', [
                    'lineName'  => $exception->getCollision()->trip->linename,
                    'validated' => $validated,
                ]);

        } catch (TrainCheckinAlreadyExistException) {
            return redirect()->back()
                             ->with('error', __('messages.exception.already-checkedin'));
        } catch (AlreadyCheckedInException) {
            $message = __('messages.exception.already-checkedin') . ' ' . __('messages.exception.maybe-too-fast');
            return redirect()->back()
                             ->with('error', $message);
        } catch (Throwable $exception) {
            report($exception);
            return redirect()
                ->back()
                ->with('error', errorMessage($exception));
        }
    }

    public function StationByCoordinates(Request $request): RedirectResponse {
        $validatedInput = $request->validate([
                                                 'latitude'  => ['required', 'numeric', 'min:-90', 'max:90'],
                                                 'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
                                             ]);
        try {
            $nearestStation = HafasController::getNearbyStations(
                $validatedInput['latitude'], $validatedInput['longitude'], 1
            )->first();
        } catch (HafasException) {
            return back()->with('error', __('messages.exception.generalHafas'));
        }

        if ($nearestStation === null) {
            return back()->with('error', __('controller.transport.no-station-found'));
        }

        return redirect()->route('trains.stationboard', [
            'station'  => $nearestStation->ibnr,
            'provider' => 'train'
        ]);
    }

    public function TrainTrip(Request $request): Renderable|RedirectResponse {
        $validated = $request->validate([
                                            'tripID'          => ['required'],
                                            'lineName'        => ['required'],
                                            'start'           => ['required', 'numeric'],
                                            'departure'       => ['required', 'date'],
                                            'searchedStation' => ['nullable', 'exists:train_stations,id'],
                                        ]);

        try {
            $startStation = Station::where('ibnr', $validated['start'])->first();
            if ($startStation === null) {
                // in long term to support multiple data providers we only support IDs here - no IBNRs.
                $startStation = Station::findOrFail($validated['start']);
            }
            $departure = Carbon::parse($validated['departure']);

            $trip = TrainCheckinController::getHafasTrip(
                $validated['tripID'],
                $validated['lineName'],
                $startStation->id,
            );

            $encounteredStart = false;
            $stopovers = $trip->stopovers
                ->filter(function(Stopover $stopover) use ($departure, $startStation, &$encounteredStart): bool {
                    if (!$encounteredStart) { // this assumes stopovers being ordered correctly
                        $encounteredStart = $stopover->departure_planned == $departure && $stopover->station->is($startStation);
                        return false;
                    }
                    return true;
                });

            // Find out where this train terminates and offer this as a "fast check-in" option.
            $lastStopover = $trip->stopovers
                /*->filter(function(Stopover $stopover) {
                    return !$stopover->isArrivalCancelled;
                })*/
                ->last();

            return view('trip', [
                'hafasTrip'       => $trip,
                'stopovers'       => $stopovers,
                'startStation'    => $startStation,
                'searchedStation' => isset($validated['searchedStation']) ? Station::findOrFail($validated['searchedStation']) : null,
                'lastStopover'    => $lastStopover,
            ]);
        } catch (HafasException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (StationNotOnTripException) {
            return redirect()->back()->with('error', __('controller.transport.not-in-stopovers'));
        }
    }

    public function TrainCheckin(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'tripID'            => ['required'],
                                            'start'             => ['required', 'numeric'], //origin station ID (or IBNR - in long term to support multiple data providers we only support IDs here)
                                            'departure'         => ['required', 'date'],
                                            'destination'       => ['required', 'numeric'], //Destination station ID (or IBNR - in long term to support multiple data providers we only support IDs here)
                                            'arrival'           => ['required', 'date'],
                                            'chainId'           => ['nullable', 'exists:travel_chains,id'],
                                            'planned'           => ['nullable'],
                                            'body'              => ['nullable', 'max:280'],
                                            // 'business_check'    => ['required', new Enum(Business::class)],
                                            // 'checkinVisibility' => ['nullable', new Enum(StatusVisibility::class)],
                                            'tweet_check'       => ['nullable', 'max:2'],
                                            'toot_check'        => ['nullable', 'max:2'],
                                            'chainPost_check'   => ['nullable', 'max:2'],
                                            'event'             => ['nullable', 'numeric', 'exists:events,id'],
                                            'force'             => ['nullable'],
                                        ]);

        $travelChain = isset($validated['chainId']) ? TravelChain::find($validated['chainId']) : null;
        $user = Auth::user();
        if(isset($travelChain) && $travelChain->user->isNot($user)) {
            $travelChain = null;
        }

        try {
            $backendResponse = TrainCheckinController::checkin(
                user:         $user,
                trip:         Trip::where('trip_id', $validated['tripID'])->first(),
                origin:       Station::where('ibnr', $validated['start'])->first() ?? Station::findOrFail($validated['start']),
                departure:    Carbon::parse($validated['departure']),
                destination:  Station::where('ibnr', $validated['destination'])->first() ?? Station::findOrFail($validated['destination']),
                arrival:      Carbon::parse($validated['arrival']),
                travelChain:  $travelChain,
                planned:      isset($validated['planned']) ?? false,
                travelReason: Business::PRIVATE, // Business::from($validated['business_check']),
                visibility:   StatusVisibility::PRIVATE, // StatusVisibility::tryFrom($validated['checkinVisibility'] ?? StatusVisibility::PUBLIC->value), //FGLTODO-LP: bug reporten? das mit ->value scheint falsch zu sein
                body:         $validated['body'] ?? null,
                event:        isset($validated['event']) ? Event::find($validated['event']) : null,
                force: isset($validated['force']),
                postOnMastodon: isset($request->toot_check),
                shouldChain: isset($request->chainPost_check),
            );

            $checkin = $backendResponse['status']->checkin;

            $checkinSuccess = new CheckinSuccess(
                id:                   $backendResponse['status']->id,
                distance:             $checkin->distance,
                duration:             $checkin->duration,
                points:               $checkin->points,
                pointReason:          $backendResponse['points']->reason,
                lineName:             $checkin->trip->linename,
                socialText:           $backendResponse['status']->socialText,
                alsoOnThisConnection: $checkin->alsoOnThisConnection,
                event:                $checkin->event,
                forced: isset($validated['force'])
            );
            return redirect()->route('status', ['id' => $backendResponse['status']->id])
                             ->with('checkin-success', (clone $checkinSuccess));

        } catch (CheckInCollisionException $exception) {
            return redirect()
                ->route('dashboard')
                ->with('checkin-collision', [
                    'lineName'  => $exception->getCollision()->trip->linename,
                    'validated' => $validated,
                ]);

        } catch (TrainCheckinAlreadyExistException) {
            return redirect()->route('dashboard')
                             ->with('error', __('messages.exception.already-checkedin'));
        } catch (AlreadyCheckedInException) {
            $message = __('messages.exception.already-checkedin') . ' ' . __('messages.exception.maybe-too-fast');
            return redirect()->route('dashboard')
                             ->with('error', $message);
        } catch (Throwable $exception) {
            report($exception);
            return redirect()
                ->route('dashboard')
                ->with('error', $exception->getMessage());
        }
    }

    public function setTrainHome(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'stationName' => ['required', 'max:255']
                                        ]);

        try {
            $station = HafasController::getStations(query: $validated['stationName'], results: 1)->first();
            if ($station === null) {
                return redirect()->back()->with(['error' => __('messages.exception.general')]);
            }
            $station = HomeController::setHome(auth()->user(), $station);

            return redirect()->back()->with(['success' => __('user.home-set', ['station' => $station->name])]);
        } catch (HafasException) {
            return redirect()->back()->with(['error' => __('messages.exception.generalHafas')]);
        }
    }
}
