<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Response;

class ShipmentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Shipment::class);

        return response('Admin shipment index placeholder');
    }

    public function show(Shipment $shipment): Response
    {
        $this->authorize('view', $shipment);

        return response("Admin shipment show placeholder: {$shipment->getKey()}");
    }

    public function update(Shipment $shipment): Response
    {
        $this->authorize('update', $shipment);

        return response("Admin shipment update placeholder: {$shipment->getKey()}");
    }
}
