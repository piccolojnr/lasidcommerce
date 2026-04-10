<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShipmentController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Shipment::class);

        return Inertia::render('admin/shipments/index');
    }

    public function show(Shipment $shipment): InertiaResponse
    {
        $this->authorize('view', $shipment);

        return Inertia::render('admin/shipments/show');
    }

    public function update(Shipment $shipment): Response
    {
        $this->authorize('update', $shipment);

        return response("Admin shipment update placeholder: {$shipment->getKey()}");
    }
}
