<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;

class CreateProductOptionValueAction
{
    public function execute(ProductOptionType $optionType, array $attributes): ProductOptionValue
    {
        return ProductOptionValue::query()->create(array_merge($attributes, [
            'option_type_id' => $optionType->getKey(),
        ]));
    }
}
