<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;

class CreateProductVariantAction
{
    public function execute(Product $product, array $attributes): ProductVariant
    {
        $optionValueIds = collect($attributes['option_value_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        unset($attributes['option_value_ids']);

        return DB::transaction(function () use ($product, $attributes, $optionValueIds) {
            $variant = ProductVariant::query()->create(array_merge($attributes, [
                'product_id' => $product->getKey(),
            ]));

            if ($optionValueIds !== []) {
                $variant->optionValues()->sync($optionValueIds);
            }

            if ($product->track_inventory) {
                StockItem::query()->firstOrCreate([
                    'product_id' => $product->getKey(),
                    'product_variant_id' => $variant->getKey(),
                ]);
            }

            return $variant->load('optionValues.optionType', 'stockItems');
        });
    }
}
