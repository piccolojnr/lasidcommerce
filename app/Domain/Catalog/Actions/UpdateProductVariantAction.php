<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class UpdateProductVariantAction
{
    public function execute(ProductVariant $variant, array $attributes): ProductVariant
    {
        $optionValueIds = array_key_exists('option_value_ids', $attributes)
            ? collect($attributes['option_value_ids'])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all()
            : null;
        unset($attributes['option_value_ids']);

        return DB::transaction(function () use ($variant, $attributes, $optionValueIds) {
            $variant->fill($attributes);
            $variant->save();

            if ($optionValueIds !== null) {
                $variant->optionValues()->sync($optionValueIds);
            }

            return $variant->load('optionValues.optionType', 'stockItems');
        });
    }
}
