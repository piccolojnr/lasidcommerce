<?php

namespace App\Domain\Catalog\Services;

use App\Models\Product;

class ProductAvailabilityResolver
{
    public function isAvailable(Product $product): bool
    {
        return $product->isActive() && (! $product->track_inventory || $product->stockItems()->exists());
    }
}
