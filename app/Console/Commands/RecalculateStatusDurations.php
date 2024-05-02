<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use App\Models\Status;
use Illuminate\Console\Command;

class RecalculateStatusDurations extends Command
{
    protected $signature   = 'trwl:recalculateStatusDurations {id*}';
    protected $description = 'Recalculate distance for status id';

    public function handle(): void {
        $ids      = $this->arguments()['id'];
        $statuses = Status::whereIn('id', $ids)->get();
        $this->info(sprintf('Found %d of %d statuses', count($ids), count($statuses)));
        $this->newLine(3);
        foreach ($statuses as $status) {
            $checkin = $status->checkin;
            $oldDuration = $checkin->duration;
            $oldDurationPlanned = $checkin->durationPlanned;

            // kopiert aus TrainCheckinController als quick fix
            $departure = $checkin->manual_departure ?? $checkin->originStopover->departure ?? $checkin->departure;
            $arrival   = $checkin->manual_arrival ?? $checkin->destinationStopover->arrival ?? $checkin->arrival;
            $departurePlanned = $checkin->originStopover->departure_planned ?? $checkin->departure;
            $arrivalPlanned   = $checkin->destinationStopover->arrival_planned ?? $checkin->arrival;
            $duration  = $arrival->diffInMinutes($departure);
            $durationPlanned  = $arrivalPlanned->diffInMinutes($departurePlanned);
            //don't use eloquent here, because it would trigger the observer (and this function) again
            DB::table('train_checkins')->where('id', $checkin->id)->update(['duration' => $duration, 'duration_planned' => $durationPlanned]);

            $this->info(sprintf('#%d: %d -> %d', $status->id, $oldDuration, $duration));
            $this->info(sprintf('#%d: %d -> %d (planned)', $status->id, $oldDurationPlanned, $durationPlanned));
            $this->newLine();
        }
    }
}
