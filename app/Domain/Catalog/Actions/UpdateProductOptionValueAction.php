<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductOptionValue;

class UpdateProductOptionValueAction
{
    public function execute(ProductOptionValue $optionValue, array $attributes): ProductOptionValue
    {
        $optionValue->fill($attributes);
        $optionValue->save();

        return $optionValue;
    }
}
