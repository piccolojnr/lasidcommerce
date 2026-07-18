<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;
use App\Models\ProductOptionType;

class CreateProductOptionTypeAction
{
    public function execute(Product $product, array $attributes): ProductOptionType
    {
        return ProductOptionType::query()->create(array_merge($attributes, [
            'product_id' => $product->getKey(),
        ]));
    }
}
