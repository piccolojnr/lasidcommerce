<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;

class UpdateProductAction
{
    public function execute(Product $product, array $attributes): Product
    {
        $product->fill($attributes);

        return $product;
    }
}
