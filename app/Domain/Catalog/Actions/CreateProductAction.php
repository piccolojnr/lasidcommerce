<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;

class CreateProductAction
{
    public function execute(array $attributes): Product
    {
        return new Product($attributes);
    }
}
