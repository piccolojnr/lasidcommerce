<?php

namespace App\Domain\Inventory\Queries;

use App\Models\StockItem;

class GetAdminStockItemDetailQuery
{
    public function get(StockItem $stockItem): StockItem
    {
        return $stockItem->load([
            'product',
            'productVariant',
            'stockMovements' => fn ($query) => $query->latest('created_at')->with('creator'),
        ]);
    }
}
