<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\ExportableColumn;
use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FullExportController extends Controller
{
    public function generateStatusExport(Request $request): JsonResponse|StreamedResponse|Response|RedirectResponse {
        return ExportBackend::generateFullStatusExport();
    }

    public function generateTravelchainExport(Request $request): JsonResponse|StreamedResponse|Response|RedirectResponse {
        return ExportBackend::generateFullTravelchainExport();
    }
}
