<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductVariant;

class DeleteProductVariantAction
{
    public function execute(ProductVariant $variant): bool|null
    {
        return $variant->delete();
    }
}
