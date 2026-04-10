<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingZoneRequest;
use App\Http\Requests\Admin\UpdateShippingZoneRequest;
use App\Models\ShippingZone;
use Illuminate\Http\Response;

class ShippingZoneController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ShippingZone::class, 'zone');
    }

    public function index(): Response
    {
        return response('Admin shipping zone index placeholder');
    }

    public function create(): Response
    {
        return response('Admin shipping zone create placeholder');
    }

    public function store(StoreShippingZoneRequest $request): Response
    {
        return response('Admin shipping zone store placeholder', Response::HTTP_CREATED);
    }

    public function show(ShippingZone $zone): Response
    {
        return response("Admin shipping zone show placeholder: {$zone->getKey()}");
    }

    public function edit(ShippingZone $zone): Response
    {
        return response("Admin shipping zone edit placeholder: {$zone->getKey()}");
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $zone): Response
    {
        return response("Admin shipping zone update placeholder: {$zone->getKey()}");
    }

    public function destroy(ShippingZone $zone): Response
    {
        return response()->noContent();
    }
}
