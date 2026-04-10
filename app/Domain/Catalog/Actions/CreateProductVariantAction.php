<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;
use App\Models\ProductVariant;

class CreateProductVariantAction
{
    public function execute(Product $product, array $attributes): ProductVariant
    {
        return new ProductVariant(array_merge($attributes, [
            'product_id' => $product->getKey(),
        ]));
    }
}
