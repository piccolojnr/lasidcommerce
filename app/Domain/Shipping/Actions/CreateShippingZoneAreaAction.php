<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;

class CreateShippingZoneAreaAction
{
    public function execute(ShippingZone $zone, array $attributes): ShippingZoneArea
    {
        return new ShippingZoneArea(array_merge($attributes, [
            'shipping_zone_id' => $zone->getKey(),
        ]));
    }
}
