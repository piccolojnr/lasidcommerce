<?php

namespace App\Domain\Inventory\Actions;

use App\Models\StockItem;

class UpdateStockItemAction
{
    public function execute(StockItem $stockItem, array $attributes): StockItem
    {
        $stockItem->fill([
            'reorder_level' => $attributes['reorder_level'] ?? null,
        ]);
        $stockItem->save();

        return $stockItem->fresh();
    }
}
