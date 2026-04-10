<?php

namespace App\Domain\Inventory\Actions;

use App\Models\StockItem;

class ReserveStockAction
{
    public function execute(StockItem $stockItem, int $quantity): StockItem
    {
        $stockItem->quantity_reserved += $quantity;

        return $stockItem;
    }
}
