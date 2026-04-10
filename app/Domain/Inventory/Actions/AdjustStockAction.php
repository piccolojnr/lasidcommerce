<?php

namespace App\Domain\Inventory\Actions;

use App\Models\StockItem;

class AdjustStockAction
{
    public function execute(StockItem $stockItem, int $quantity): StockItem
    {
        $stockItem->quantity_on_hand += $quantity;

        return $stockItem;
    }
}
