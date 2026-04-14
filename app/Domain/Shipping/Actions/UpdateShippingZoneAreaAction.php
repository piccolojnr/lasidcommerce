<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingZoneArea;

class UpdateShippingZoneAreaAction
{
    public function execute(ShippingZoneArea $area, array $attributes): ShippingZoneArea
    {
        $area->fill($attributes);
        $area->save();

        return $area;
    }
}
