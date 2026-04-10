<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Response;

class ShipmentStatusController extends Controller
{
    public function update(Shipment $shipment): Response
    {
        $this->authorize('update', $shipment);

        return response("Admin shipment status update placeholder: {$shipment->getKey()}");
    }
}
