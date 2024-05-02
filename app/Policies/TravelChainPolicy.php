<?php

namespace App\Policies;

// use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\User\BlockController;
use App\Models\Status;
use App\Models\TravelChain;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TravelChainPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param User|null $user
     * @param TravelChain    $chain
     *
     * @return Response|bool
     * @todo implement blocked and muted
     */
    public function view(?User $user, TravelChain $chain): Response|bool {
        // Case 1: User is unauthenticated
        if ($user === null) {
            return false;
        }

        // Case 2: Status belongs to the user
        if ($user->id === $chain->user_id) {
            return Response::allow();
        }

        return Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User   $user
     * @param TravelChain $chain
     *
     * @return bool
     * @todo test
     */
    public function update(User $user, TravelChain $chain): bool {
        return $user->id === $chain->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User   $user
     * @param TravelChain $chain
     *
     * @return bool
     * @todo test
     */
    public function delete(User $user, TravelChain $chain): bool {
        return $user->id === $chain->user_id;
    }
}
