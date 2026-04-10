<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductOptionType;

class UpdateProductOptionTypeAction
{
    public function execute(ProductOptionType $optionType, array $attributes): ProductOptionType
    {
        $optionType->fill($attributes);

        return $optionType;
    }
}
