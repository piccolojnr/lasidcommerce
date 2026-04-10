<?php

namespace App\Domain\Shipping\Actions;

use App\Models\WarehouseLocation;

class UpdateWarehouseLocationAction
{
    public function execute(WarehouseLocation $warehouseLocation, array $attributes): WarehouseLocation
    {
        $warehouseLocation->fill($attributes);

        return $warehouseLocation;
    }
}
