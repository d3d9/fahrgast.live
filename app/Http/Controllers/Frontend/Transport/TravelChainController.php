<?php

namespace App\Http\Controllers\Frontend\Transport;

use App\Dto\CheckinSuccess;
use App\Enum\Business;
use App\Enum\Likert5;
use App\Enum\StatusVisibility;
use App\Enum\TravelChainFinished;
use App\Events\TravelChainUpdateEvent;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\TravelChain;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class TravelChainController extends Controller
{

    public function updateTravelChain(Request $request): JsonResponse|RedirectResponse {
        $validated = $request->validate([
                                            'chainId'               => ['required', 'exists:travel_chains,id'],
                                            'title'                 => ['required', 'max:255'],
                                            'body'                  => ['nullable', 'max:280'],
                                            'business_check'        => ['required', new Enum(Business::class)], //TODO: Why is this not CamelCase?
                                            'reliability_importance' => ['required', new Enum(Likert5::class)],
                                            'planned_for_reliability' => ['required', new Enum(Likert5::class)],
                                            // 'checkinVisibility'     => ['required', new Enum(StatusVisibility::class)],
                                        ]);

        try {
            $chain = TravelChain::findOrFail($validated['chainId']);
            $this->authorize('update', $chain);
            // FGLTODO-LP: business bei den statuses später noch setzen anhand diesem hier
            $chain->update([
                                'title'       => $validated['title'] ?? null,
                                'body'       => $validated['body'] ?? null,
                                'business'   => Business::from($validated['business_check']),
                                'reliability_importance' => Likert5::from($validated['reliability_importance']),
                                'planned_for_reliability'   => Likert5::from($validated['planned_for_reliability']),
                            ]);

            TravelChainUpdateEvent::dispatch($chain->refresh());

            return redirect()->route('travelchain', ['id' => $chain->id])
                             ->with('success', __('travelchain.update.success'));
        } catch (ModelNotFoundException|PermissionException) {
            return redirect()->back()->with('alert-danger', __('messages.exception.general'));
        } catch (AuthorizationException) {
            return redirect()->back()->with('alert-danger', __('error.status.not-authorized'));
        }
    }

    public function updateTravelChainFinish(Request $request): JsonResponse|RedirectResponse {
        $validated = $request->validate([
                                            'chainId'       => ['required', 'exists:travel_chains,id'],
                                            'finished'      => ['nullable', new Enum(TravelChainFinished::class)],
                                            'felt_punctual' => ['required_if:finished,arrived,arrived_diff_dest', new Enum(Likert5::class)],
                                            'felt_stressed' => ['required_with:finished', new Enum(Likert5::class)],
                                        ]);

        try {
            $chain = TravelChain::findOrFail($validated['chainId']);
            $this->authorize('update', $chain);

            $chainUpdate = [
                                'finished'   => isset($validated['finished']) ? TravelChainFinished::from($validated['finished']) : null,
                                'felt_punctual' => null, 'felt_stressed' => null,
                           ];

            if ($chainUpdate['finished'] === null) {

            } else {
                if ($chainUpdate['finished']->isArrived()) {
                    $chainUpdate['felt_punctual'] = Likert5::from($validated['felt_punctual']);
                }
                $chainUpdate['felt_stressed'] = Likert5::from($validated['felt_stressed']);
            }

            $chain->update($chainUpdate);

            TravelChainUpdateEvent::dispatch($chain->refresh());

            return redirect()->route('travelchain', ['id' => $chain->id])
                             ->with('success', __('travelchain.updateFinish.success'));
        } catch (ModelNotFoundException|PermissionException) {
            return redirect()->back()->with('alert-danger', __('messages.exception.general'));
        } catch (AuthorizationException) {
            return redirect()->back()->with('alert-danger', __('error.status.not-authorized'));
        }
    }
}
