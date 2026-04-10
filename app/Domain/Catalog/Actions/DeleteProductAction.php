<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;

class DeleteProductAction
{
    public function execute(Product $product): bool|null
    {
        return $product->delete();
    }
}
