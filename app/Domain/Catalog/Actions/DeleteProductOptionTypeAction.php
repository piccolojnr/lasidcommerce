<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductOptionType;

class DeleteProductOptionTypeAction
{
    public function execute(ProductOptionType $optionType): ?bool
    {
        return $optionType->delete();
    }
}
