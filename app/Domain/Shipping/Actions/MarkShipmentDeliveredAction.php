<?php

namespace App\Domain\Shipping\Actions;

use App\Models\Shipment;

class MarkShipmentDeliveredAction
{
    public function execute(Shipment $shipment): Shipment
    {
        $shipment->status = 'delivered';

        return $shipment;
    }
}
