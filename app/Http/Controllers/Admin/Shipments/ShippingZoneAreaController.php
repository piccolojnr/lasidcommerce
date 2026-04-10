<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingZoneAreaRequest;
use App\Http\Requests\Admin\UpdateShippingZoneAreaRequest;
use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;
use Illuminate\Http\Response;

class ShippingZoneAreaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ShippingZoneArea::class, 'area');
    }

    public function index(ShippingZone $zone): Response
    {
        $this->authorize('view', $zone);

        return response("Admin shipping zone area index placeholder: {$zone->getKey()}");
    }

    public function create(ShippingZone $zone): Response
    {
        $this->authorize('update', $zone);

        return response("Admin shipping zone area create placeholder: {$zone->getKey()}");
    }

    public function store(StoreShippingZoneAreaRequest $request, ShippingZone $zone): Response
    {
        $this->authorize('update', $zone);

        return response("Admin shipping zone area store placeholder: {$zone->getKey()}", Response::HTTP_CREATED);
    }

    public function show(ShippingZoneArea $area): Response
    {
        return response("Admin shipping zone area show placeholder: {$area->getKey()}");
    }

    public function edit(ShippingZoneArea $area): Response
    {
        return response("Admin shipping zone area edit placeholder: {$area->getKey()}");
    }

    public function update(UpdateShippingZoneAreaRequest $request, ShippingZoneArea $area): Response
    {
        return response("Admin shipping zone area update placeholder: {$area->getKey()}");
    }

    public function destroy(ShippingZoneArea $area): Response
    {
        return response()->noContent();
    }
}
