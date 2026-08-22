<?php

namespace App\Http\Controllers\Backend\Shared;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;

/**
 * Small reference-data JSON endpoints backing country → state → city cascades
 * in Buyer/Supplier/Admin forms (RFQ delivery, account locations, profiles).
 * Read-only lookup data, no account scoping required.
 */
class LocationLookupController extends Controller
{
    public function states(Country $country): JsonResponse
    {
        return response()->json(
            $country->states()->active()->get(['id', 'name'])
        );
    }

    public function cities(State $state): JsonResponse
    {
        return response()->json(
            $state->cities()->active()->get(['id', 'name'])
        );
    }
}
