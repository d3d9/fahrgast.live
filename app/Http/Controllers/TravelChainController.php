<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\TravelChainDeleteEvent;
use App\Events\TravelChainUpdateEvent;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Models\Event;
use App\Models\Like;
use App\Models\Status;
use App\Models\TravelChain;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TravelChainController extends Controller
{
    /**
     * Authorization in Frontend required! $this->authorize('view', $status);
     *
     * @param int $chainId
     *
     * @return TravelChain
     * @throws HttpException
     * @throws ModelNotFoundException
     * @api v1
     * @frontend
     */
    public static function getTravelChain(int $chainId): TravelChain {
        return TravelChain::where('id', $chainId)
                     ->with([
                                'statuses'
                            ])
                     ->firstOrFail();
    }

    /**
     * @param User $user
     * @param int  $chainId
     *
     * @return bool|null
     * @throws PermissionException|ModelNotFoundException
     */
    public static function DeleteTravelChain(User $user, int $chainId): ?bool {
        $chain = TravelChain::find($chainId);

        if ($chain === null) {
            throw new ModelNotFoundException();
        }
        if ($user->id != $chain->user->id) {
            throw new PermissionException();
        }
        $chain->delete();

        TravelChainDeleteEvent::dispatch($chain);

        return true;
    }

    public static function createTravelChain(
        User             $user,
        Business         $business = null,
        // StatusVisibility $visibility,
        string           $body = null,
        string           $title = null,
    ): TravelChain {
        return TravelChain::create([
                                  'user_id'    => $user->id,
                                  'title'      => isset($title) ? $title : ("Reisekette vom " . userTime(Carbon::now(), __('datetime-format'))),
                                  'body'       => isset($body) ? $body : "",
                                  'business'   => $business,
                                  // 'visibility' => $visibility,
                              ]);
    }
}
