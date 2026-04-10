<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingZoneArea;

class DeleteShippingZoneAreaAction
{
    public function execute(ShippingZoneArea $area): bool|null
    {
        return $area->delete();
    }
}
