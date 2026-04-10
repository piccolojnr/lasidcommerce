<?php

namespace App\Domain\Inventory\Services;

use App\Models\StockItem;

class InventoryManager
{
    public function sync(StockItem $stockItem): StockItem
    {
        return $stockItem;
    }
}
