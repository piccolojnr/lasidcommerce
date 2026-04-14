<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;

class AttachShippingMethodToZoneAction
{
    public function execute(ShippingZone $zone, ShippingMethod $method): void
    {
        $zone->shippingMethods()->syncWithoutDetaching([$method->getKey()]);
    }
}
