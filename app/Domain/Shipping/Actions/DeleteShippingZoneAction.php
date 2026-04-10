<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingZone;

class DeleteShippingZoneAction
{
    public function execute(ShippingZone $zone): bool|null
    {
        return $zone->delete();
    }
}
