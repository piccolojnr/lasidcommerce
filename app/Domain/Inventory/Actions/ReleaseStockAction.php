<?php

namespace App\Domain\Inventory\Actions;

use App\Models\StockItem;

class ReleaseStockAction
{
    public function execute(StockItem $stockItem, int $quantity): StockItem
    {
        $stockItem->quantity_reserved = max($stockItem->quantity_reserved - $quantity, 0);

        return $stockItem;
    }
}
