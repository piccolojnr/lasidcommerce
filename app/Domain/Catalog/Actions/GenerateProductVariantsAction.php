<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;
use App\Models\ProductOptionType;
use App\Models\ProductVariant;
use App\Models\StockItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateProductVariantsAction
{
    /**
     * Generate the cartesian product of all option values and bulk-create any
     * combination that does not already exist as a variant.
     *
     * Returns an array with:
     *   - created (int)  number of new variants created
     *   - skipped (int)  number of combinations that already existed
     */
    public function execute(Product $product): array
    {
        $optionTypes = $product->optionTypes()->with('optionValues')->get();

        if ($optionTypes->isEmpty()) {
            return ['created' => 0, 'skipped' => 0];
        }

        // Build the value-id axes: [[1,2,3], [4,5], …]
        $axes = $optionTypes
            ->filter(fn (ProductOptionType $ot) => $ot->optionValues->isNotEmpty())
            ->map(fn (ProductOptionType $ot) => $ot->optionValues->all())
            ->values();

        if ($axes->isEmpty()) {
            return ['created' => 0, 'skipped' => 0];
        }

        $combinations = $this->cartesian($axes);

        // Load existing variants with their option value ids so we can skip dupes
        $existing = $product->variants()
            ->with('optionValues:id')
            ->get()
            ->map(fn (ProductVariant $v) => $v->optionValues->pluck('id')->sort()->values()->implode('-'))
            ->flip(); // key → true for O(1) lookup

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($product, $combinations, $existing, &$created, &$skipped) {
            foreach ($combinations as $combo) {
                $valueIds = collect($combo)->pluck('id')->sort()->values();
                $key = $valueIds->implode('-');

                if ($existing->has($key)) {
                    $skipped++;
                    continue;
                }

                // Build a human-readable name from the option values
                $name = collect($combo)->pluck('value')->implode(' / ');

                // Auto-generate a SKU from product SKU + value slugs
                $skuSuffix = collect($combo)
                    ->map(fn ($v) => Str::upper(Str::substr(Str::slug($v->value, ''), 0, 4)))
                    ->implode('-');
                $sku = $product->sku . '-' . $skuSuffix;

                // Ensure SKU uniqueness by appending a counter if needed
                $sku = $this->uniqueSku($sku);

                $variant = ProductVariant::query()->create([
                    'product_id' => $product->getKey(),
                    'name'       => $name,
                    'sku'        => $sku,
                    'is_active'  => true,
                ]);

                $variant->optionValues()->sync($valueIds->all());

                if ($product->track_inventory) {
                    StockItem::query()->firstOrCreate([
                        'product_id'         => $product->getKey(),
                        'product_variant_id' => $variant->getKey(),
                    ]);
                }

                $created++;
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Compute the cartesian product of $axes.
     * Each axis is a collection/array of ProductOptionValue models.
     *
     * @param  Collection<int, array>  $axes
     * @return array<int, array<int, \App\Models\ProductOptionValue>>
     */
    private function cartesian(Collection $axes): array
    {
        $result = [[]];

        foreach ($axes as $axis) {
            $next = [];
            foreach ($result as $partial) {
                foreach ($axis as $value) {
                    $next[] = array_merge($partial, [$value]);
                }
            }
            $result = $next;
        }

        // Remove the seed empty array
        return array_filter($result, fn ($c) => count($c) > 0);
    }

    /** Append a numeric suffix until the SKU is unique in the table. */
    private function uniqueSku(string $base): string
    {
        $sku = $base;
        $counter = 2;

        while (ProductVariant::query()->where('sku', $sku)->exists()) {
            $sku = $base . '-' . $counter;
            $counter++;
        }

        return $sku;
    }
}
