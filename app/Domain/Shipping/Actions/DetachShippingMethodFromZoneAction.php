<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;

class DetachShippingMethodFromZoneAction
{
    public function execute(ShippingZone $zone, ShippingMethod $method): void
    {
        $zone->shippingMethods()->detach($method->getKey());
    }
}
