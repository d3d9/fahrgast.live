<?php


namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TravelChainController as TravelChainBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\TravelChain;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use OpenApi\Annotations as OA;

class TravelChainController extends Controller
{
    public function destroy(int $chainId): JsonResponse {
        try {
            TravelChainBackend::DeleteTravelChain(Auth::user(), $chainId);
            return $this->sendResponse(['message' => __('controller.chain.delete-ok')]);
        } catch (PermissionException) {
            return $this->sendError('You are not allowed to delete this travel chain.', 403);
        } catch (ModelNotFoundException) {
            return $this->sendError('No travel chain found for this id.');
        }
    }

}
