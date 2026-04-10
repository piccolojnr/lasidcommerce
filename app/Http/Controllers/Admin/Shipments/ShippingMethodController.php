<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingMethodRequest;
use App\Http\Requests\Admin\UpdateShippingMethodRequest;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Http\Response;

class ShippingMethodController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ShippingMethod::class, 'method');
    }

    public function index(ShippingZone $zone): Response
    {
        $this->authorize('view', $zone);

        return response("Admin shipping method index placeholder: {$zone->getKey()}");
    }

    public function create(ShippingZone $zone): Response
    {
        $this->authorize('update', $zone);

        return response("Admin shipping method create placeholder: {$zone->getKey()}");
    }

    public function store(StoreShippingMethodRequest $request, ShippingZone $zone): Response
    {
        $this->authorize('update', $zone);

        return response("Admin shipping method store placeholder: {$zone->getKey()}", Response::HTTP_CREATED);
    }

    public function show(ShippingMethod $method): Response
    {
        return response("Admin shipping method show placeholder: {$method->getKey()}");
    }

    public function edit(ShippingMethod $method): Response
    {
        return response("Admin shipping method edit placeholder: {$method->getKey()}");
    }

    public function update(UpdateShippingMethodRequest $request, ShippingMethod $method): Response
    {
        return response("Admin shipping method update placeholder: {$method->getKey()}");
    }

    public function destroy(ShippingMethod $method): Response
    {
        return response()->noContent();
    }
}
