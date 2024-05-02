<?php
namespace App\Collections;

use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class StatusCollection extends Collection {

    // sort expected

    public function firstStopover() {
        return $this->first()?->checkin->originStopover;
    }

    public function depPlanned() {
        return $this->firstStopover()?->departure_planned;
    }

    public function lastStopover() {
        return $this->last()?->checkin->destinationStopover;
    }

    public function arrPlanned() {
        return $this->lastStopover()?->arrival_planned;
    }

    public function depReal() {
        return $this->first()?->checkin->displayDeparture->time;
    }

    public function arrReal() {
        return $this->last()?->checkin->displayArrival->time;
    }

    public function distance() {
        return $this->sum('checkin.distance');
    }

    public function checkinDurationMins() {
        return $this->sum('checkin.duration');
    }

    public function checkinPlannedDurationMins() {
        return $this->sum('checkin.durationPlanned');
    }
    
}