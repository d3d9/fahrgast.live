<?php

namespace App\Http\Controllers\Frontend\Transport;

use App\Dto\CheckinSuccess;
use App\Enum\Business;
use App\Enum\NotTakenReason;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TravelChainController;
use App\Models\Status;
use App\Models\TravelChain;
use App\Models\Stopover;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{

    public function updateStatus(Request $request): JsonResponse|RedirectResponse {
        $validated = $request->validate([
                                            'statusId'              => ['required', 'exists:statuses,id'],
                                            'chainId'               => ['nullable', 'exists:travel_chains,id'],
                                            'planned'               => ['nullable'],
                                            'body'                  => ['nullable', 'max:280'],
                                            'manualDeparture'       => ['nullable', 'date'],
                                            'manualArrival'         => ['nullable', 'date'],
                                            //'business_check'        => ['required', new Enum(Business::class)], //TODO: Why is this not CamelCase?
                                            //'checkinVisibility'     => ['required', new Enum(StatusVisibility::class)],
                                            // 'destinationStopoverId' => ['nullable', 'exists:train_stopovers,id'],
                                        ]);

        try {
            $status = Status::findOrFail($validated['statusId']);
            $this->authorize('update', $status);

            $travelChain = isset($validated['chainId']) ? TravelChain::find($validated['chainId']) : null;
            $user = Auth::user();
            if(isset($travelChain) && $travelChain->user->isNot($user)) {
                $travelChain = null;
            }

            if (isset($travelChain)) {
                //
            } else {
                $travelChain = TravelChainController::createTravelChain($user);
            }

            $status->update([
                                'body'       => $validated['body'] ?? null,
                                'chain_id'   => $travelChain->id,
                                'planned'    => isset($validated['planned']) ?? false,
                                'business'   => $travelChain->business ?? Business::PRIVATE
                                //'business'   => Business::from($validated['business_check']),
                                //'visibility' => StatusVisibility::from($validated['checkinVisibility']),
                            ]);

            $status->checkin->update([
                                              'manual_departure' => isset($validated['manualDeparture']) ?
                                                  Carbon::parse($validated['manualDeparture'], auth()->user()->timezone) :
                                                  null,
                                              'manual_arrival'   => isset($validated['manualArrival']) ?
                                                  Carbon::parse($validated['manualArrival'], auth()->user()->timezone) :
                                                  null,
                                          ]);

            StatusUpdateEvent::dispatch($status->refresh());

            return redirect()->back()//->route('status', ['id' => $status->id])
                             ->with('success', __('status.update.success'));
        } catch (ModelNotFoundException|PermissionException) {
            return redirect()->back()->with('alert-danger', __('messages.exception.general'));
        } catch (AuthorizationException) {
            return redirect()->back()->with('alert-danger', __('error.status.not-authorized'));
        }
    }

    public function updateStatusTaken(Request $request): JsonResponse|RedirectResponse {
        $validated = $request->validate([
                                            'statusId'              => ['required', 'exists:statuses,id'],
                                            'taken'                 => ['nullable', 'boolean'],
                                            'not_taken_reason'      => ['required_if:taken,0', new Enum(NotTakenReason::class)],
                                        ]);
        try {
            $status = Status::findOrFail($validated['statusId']);
            $this->authorize('update', $status);

            $statusUpdate = [
                                'taken' => isset($validated['taken']) ? boolval($validated['taken']) : null,
                            ];
            if ($statusUpdate['taken'] === false) {
                $statusUpdate['not_taken_reason'] = NotTakenReason::tryFrom($validated['not_taken_reason'] ?? null);
            } else {
                $statusUpdate['not_taken_reason'] = null;
            }

            $status->update($statusUpdate);

            StatusUpdateEvent::dispatch($status->refresh()); // ?

            return redirect()->back()//->route('status', ['id' => $status->id])
                             ->with('success', __('status.updateTaken.success'));
        } catch (ModelNotFoundException|PermissionException) {
            return redirect()->back()->with('alert-danger', __('messages.exception.general'));
        } catch (AuthorizationException) {
            return redirect()->back()->with('alert-danger', __('error.status.not-authorized'));
        }
    }

    public function updateStatusDestination(Request $request): JsonResponse|RedirectResponse {
        $validated = $request->validate([
                                            'statusId'              => ['required', 'exists:statuses,id'],
                                            'destinationStopoverId' => ['required', 'exists:train_stopovers,id'],
                                        ]);

        try {
            $status = Status::findOrFail($validated['statusId']);
            $this->authorize('update', $status);

            if($validated['destinationStopoverId'] != $status->checkin->destinationStopover->id) {
                // FGLTODO-LP: handle planned or not etc.

                $pointReason = TrainCheckinController::changeDestination(
                    checkin:                $status->checkin,
                    newDestinationStopover: Stopover::findOrFail($validated['destinationStopoverId']),
                );
                $status->fresh();

                $checkinSuccess = new CheckinSuccess(
                    id:                   $status->id,
                    distance:             $status->checkin->distance,
                    duration:             $status->checkin->duration,
                    points:               $status->checkin->points,
                    pointReason:          $pointReason,
                    lineName:             $status->checkin->trip->linename,
                    socialText:           $status->socialText,
                    alsoOnThisConnection: $status->checkin->alsoOnThisConnection,
                    event:                $status->checkin->event,
                    forced:               false,
                    reason:               'status-updated'
                );

                // hier kein back sinnvoll
                return redirect()->route('status', ['id' => $status->id])
                                 ->with('checkin-success', (clone $checkinSuccess));
            } else {
                return redirect()->back()->with('success', 'Der Ausstieg wurde nicht geändert.');
            }
        } catch (ModelNotFoundException|PermissionException) {
            return redirect()->back()->with('alert-danger', __('messages.exception.general'));
        } catch (AuthorizationException) {
            return redirect()->back()->with('alert-danger', __('error.status.not-authorized'));
        }
    }

}
