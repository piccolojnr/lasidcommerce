<?php

namespace App\Domain\Inventory\Queries;

use App\Models\StockMovement;

class GetAdminStockMovementDetailQuery
{
    public function get(StockMovement $stockMovement): StockMovement
    {
        return $stockMovement->load([
            'creator',
            'stockItem.product',
            'stockItem.productVariant',
        ]);
    }
}
