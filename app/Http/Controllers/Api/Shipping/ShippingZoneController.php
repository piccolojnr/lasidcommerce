<?php

namespace App\Http\Controllers\Api\Shipping;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ShippingZoneController extends Controller
{
    public function index(): JsonResponse
    {
        $zones = ShippingZone::active()
            ->with(['areas' => fn ($q) => $q->orderBy('area_type')->orderBy('area_name')])
            ->orderBy('name')
            ->get()
            ->map(fn ($zone) => [
                'id'    => $zone->id,
                'name'  => $zone->name,
                'code'  => $zone->code,
                'areas' => $zone->areas->map(fn ($area) => [
                    'id'        => $area->id,
                    'area_type' => $area->area_type,
                    'area_name' => $area->area_name,
                ])->values(),
            ]);

        return ApiResponse::success($zones);
    }
}
