<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductVariant;

class UpdateProductVariantAction
{
    public function execute(ProductVariant $variant, array $attributes): ProductVariant
    {
        $variant->fill($attributes);

        return $variant;
    }
}
