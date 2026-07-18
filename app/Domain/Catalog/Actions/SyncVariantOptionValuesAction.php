<?php

namespace App\Domain\Catalog\Actions;

use App\Models\ProductVariant;

class SyncVariantOptionValuesAction
{
    public function execute(ProductVariant $variant, array $optionValueIds): ProductVariant
    {
        $optionValueIds = collect($optionValueIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $variant->optionValues()->sync($optionValueIds);
        $variant->load('optionValues');

        return $variant;
    }
}
