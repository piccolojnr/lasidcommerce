<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingZone;

class UpdateShippingZoneAction
{
    public function execute(ShippingZone $zone, array $attributes): ShippingZone
    {
        $zone->fill($attributes);

        return $zone;
    }
}
