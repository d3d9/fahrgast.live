<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\TravelChain;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TravelChainEditController extends Controller
{
    public function renderMain(Request $request): View {
        $validated    = $request->validate([
                                               'userQuery' => ['nullable', 'max:255'],
                                           ]);
        $lastChains = TravelChain::orderBy('created_at', 'desc')->limit(20);

        if (isset($validated['userQuery'])) {
            $lastChains = $lastChains->whereIn(
                'user_id',
                User::where('name', 'like', '%' . $validated['userQuery'] . '%')
                    ->orWhere('username', 'like', '%' . $validated['userQuery'] . '%')
                    ->pluck('id')
            );
        }

        return view('admin.travelchain.main', [
            'lastChains' => $lastChains->get(),
        ]);
    }

    public function renderEdit(Request $request): View {
        $validated = $request->validate([
                                            'chainId' => ['required', 'exists:travel_chains,id'],
                                        ]);

        return view('admin.travelchain.edit', [
            'chain' => TravelChain::find($validated['chainId'])
        ]);
    }

    // public function edit(Request $request): RedirectResponse {
    // }

}
