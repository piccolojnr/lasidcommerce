<?php

namespace App\Domain\Shipping\Actions;

use App\Models\WarehouseLocation;

class CreateWarehouseLocationAction
{
    public function execute(array $attributes): WarehouseLocation
    {
        return WarehouseLocation::query()->create($attributes);
    }
}
