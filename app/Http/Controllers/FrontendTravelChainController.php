<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TravelChainController as TravelChainBackend;
use App\Models\Event;
use App\Models\Status;
use App\Models\TravelChain;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FrontendTravelChainController extends Controller
{
    public function getTravelChain($chainId): Renderable {
        $chain = TravelChainBackend::getTravelChain($chainId);

        try {
            $this->authorize('view', $chain);
        } catch (AuthorizationException) {
            abort(403, __('error.status.not-authorized'));
        }

        /*
        $statuses = $chain->statuses->map(function(Status $status) {
            $status->mapLines = LocationController::forStatus($status)->getMapLines(true);
            return $status;
        });
        */

        $statusHtml = [];
        foreach($chain->statuses as $status) {
            $statusHtml[$status->id] = view('includes.status', ['status' => $status])->render();
        }

        $plannedStatuses = $chain->plannedStatuses;
        $takenStatuses = $chain->takenStatuses;

        // FGLTODO: auch für den anfang berechnen und exportieren für auswertung später ...
        $ddd = null;
        $planDestination = $plannedStatuses->lastStopover()?->station;
        $realDestination = $takenStatuses->lastStopover()?->station;

        if (isset($planDestination) && isset($realDestination)) {
            if ($planDestination->is($realDestination)) {
                $ddd = 0;
            } else {
                // https://stackoverflow.com/a/10054282
                $latFrom = deg2rad($planDestination->latitude);
                $lonFrom = deg2rad($planDestination->longitude);
                $latTo = deg2rad($realDestination->latitude);
                $lonTo = deg2rad($realDestination->longitude);

                $latDelta = $latTo - $latFrom;
                $lonDelta = $lonTo - $lonFrom;

                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

                $ddd = round($angle * 6371000);
            }
        }

        return view('travelchain', [
            'chain'    => $chain,
            'time'     => time(),
            'statusHtml' => $statusHtml,
            'ctaStatuses' => $chain->ctaStatuses,
            'pendingStatuses' => $chain->pendingStatuses,
            'pendingUnplannedStatuses' => $chain->pendingStatuses->filter(function($s) use ($chain) { return !$s->planned && !$chain->ctaStatuses->contains($s); }),
            'plannedStatuses' => $plannedStatuses,
            'takenStatuses' => $takenStatuses,
            'undefStatuses' => $chain->undefStatuses,
            'destinationDistanceDelta' => $ddd,
        ]);
    }
}
