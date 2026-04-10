<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingZone;

class CreateShippingZoneAction
{
    public function execute(array $attributes): ShippingZone
    {
        return new ShippingZone($attributes);
    }
}
