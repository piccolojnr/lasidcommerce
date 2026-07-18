<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductOptionValue;

class DeleteProductOptionValueAction
{
    public function execute(ProductOptionValue $optionValue): ?bool
    {
        return $optionValue->delete();
    }
}
