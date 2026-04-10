<?php

namespace App\Domain\Shipping\Actions;

use App\Models\Order;
use App\Models\Shipment;

class CreateShipmentAction
{
    public function execute(Order $order, array $attributes = []): Shipment
    {
        return new Shipment(array_merge($attributes, [
            'order_id' => $order->getKey(),
        ]));
    }
}
