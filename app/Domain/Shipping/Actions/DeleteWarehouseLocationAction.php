<?php

namespace App\Domain\Shipping\Actions;

use App\Models\WarehouseLocation;

class DeleteWarehouseLocationAction
{
    public function execute(WarehouseLocation $warehouseLocation): ?bool
    {
        return $warehouseLocation->delete();
    }
}
