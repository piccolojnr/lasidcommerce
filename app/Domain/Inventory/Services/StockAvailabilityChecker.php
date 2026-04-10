<?php

namespace App\Domain\Inventory\Services;

use App\Models\StockItem;

class StockAvailabilityChecker
{
    public function isAvailable(StockItem $stockItem, int $requestedQuantity): bool
    {
        return $stockItem->availableQuantity() >= $requestedQuantity;
    }
}
