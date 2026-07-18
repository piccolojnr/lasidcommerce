<?php

namespace App\Domain\Shipment\Actions;

use App\Models\Shipment;

class UpdateShipmentAction
{
    public function execute(Shipment $shipment, array $attributes): Shipment
    {
        $shipment->fill($attributes);
        $shipment->save();

        return $shipment->load('warehouseLocation', 'shippingMethod');
    }
}
