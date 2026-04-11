<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;

class ToggleProductStatusAction
{
    public function execute(Product $product): Product
    {
        $newStatus = $product->status === 'active' ? 'draft' : 'active';

        $product->update(['status' => $newStatus]);

        return $product->fresh();
    }
}
