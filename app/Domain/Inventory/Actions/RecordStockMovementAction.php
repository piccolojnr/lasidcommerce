<?php

namespace App\Domain\Inventory\Actions;

use App\Models\StockItem;
use App\Models\StockMovement;

class RecordStockMovementAction
{
    public function execute(StockItem $stockItem, array $attributes): StockMovement
    {
        return new StockMovement(array_merge($attributes, [
            'stock_item_id' => $stockItem->getKey(),
        ]));
    }
}
