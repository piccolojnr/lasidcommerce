<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;

class CreateShippingMethodAction
{
    public function execute(ShippingZone $zone, array $attributes): ShippingMethod
    {
        return ShippingMethod::query()->create(array_merge($attributes, [
            'shipping_zone_id' => $zone->getKey(),
        ]));
    }
}
