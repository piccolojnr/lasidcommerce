<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateProductOptionTypeAction;
use App\Domain\Catalog\Actions\GenerateProductVariantsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductOptionTypeWithValuesRequest;
use App\Models\Product;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProductVariantMatrixController extends Controller
{
    public function __construct(
        private GenerateProductVariantsAction $generateAction,
        private CreateProductOptionTypeAction $createOptionTypeAction,
    ) {}

    /**
     * Render the dedicated variant matrix page for a product.
     */
    public function index(Product $product): InertiaResponse
    {
        $this->authorize('update', $product);

        $product->load([
            'optionTypes.optionValues',
            'variants.optionValues.optionType',
            'variants.stockItems.stockMovements' => fn ($q) => $q->latest()->limit(10),
        ]);

        return Inertia::render('admin/catalog/products/variants', [
            'product' => $this->formatProduct($product),
            'movementTypes' => StockMovement::adminAdjustmentTypes(),
        ]);
    }

    /**
     * Generate all missing cartesian-product variant combinations.
     */
    public function generateMatrix(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $result = $this->generateAction->execute($product);

        $message = $result['created'] > 0
            ? "Generated {$result['created']} variant(s). {$result['skipped']} combination(s) already existed."
            : "All combinations already exist ({$result['skipped']} skipped).";

        return back()->with('success', $message);
    }

    /**
     * Create an option type and bulk-create its values in one request.
     */
    public function storeOptionType(StoreProductOptionTypeWithValuesRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $optionType = $this->createOptionTypeAction->execute($product, [
            'name' => $request->validated('name'),
        ]);

        $values = array_filter(array_map('trim', $request->validated('values', [])));
        foreach ($values as $value) {
            ProductOptionValue::query()->create([
                'option_type_id' => $optionType->getKey(),
                'value' => $value,
            ]);
        }

        return back()->with('success', "Option \"{$optionType->name}\" created with ".count($values).' value(s).');
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'base_price' => $product->base_price,
            'track_inventory' => $product->track_inventory,
            'option_types' => $product->optionTypes->map(
                fn (ProductOptionType $ot) => [
                    'id' => $ot->id,
                    'name' => $ot->name,
                    'values' => $ot->optionValues->map(fn (ProductOptionValue $v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                    ])->values()->all(),
                ]
            )->values()->all(),
            'variants' => $product->variants->map(
                fn (ProductVariant $v) => $this->formatVariant($v, $product->base_price)
            )->values()->all(),
        ];
    }

    private function formatVariant(ProductVariant $variant, int $basePrice): array
    {
        $stockItems = $variant->relationLoaded('stockItems') ? $variant->stockItems : $variant->stockItems()->get();

        return [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'price' => $variant->price,
            'compare_at_price' => $variant->compare_at_price,
            'cost_price' => $variant->cost_price,
            'is_active' => $variant->is_active,
            'option_value_ids' => $variant->optionValues->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'option_values' => $variant->optionValues->map(fn (ProductOptionValue $val) => [
                'id' => $val->id,
                'value' => $val->value,
                'option_type_id' => $val->option_type_id,
                'option_type_name' => $val->optionType?->name,
            ])->values()->all(),
            'inventory' => [
                'primary_stock_item_id' => $stockItems->count() === 1 ? $stockItems->first()?->getKey() : null,
                'available_quantity' => (int) $stockItems->sum(fn ($si) => $si->availableQuantity()),
                'stock_items' => $stockItems->map(fn (StockItem $si) => [
                    'id' => $si->id,
                    'quantity_on_hand' => $si->quantity_on_hand,
                    'quantity_reserved' => $si->quantity_reserved,
                    'available_quantity' => $si->availableQuantity(),
                    'reorder_level' => $si->reorder_level,
                    'movements' => ($si->relationLoaded('stockMovements') ? $si->stockMovements : $si->stockMovements()->latest()->limit(10)->get())
                        ->map(fn (StockMovement $m) => [
                            'id' => $m->id,
                            'type' => $m->type,
                            'quantity' => $m->quantity,
                            'stock_delta' => StockMovement::stockDeltaForType($m->type, $m->quantity),
                            'note' => $m->note,
                            'creator_name' => $m->creator?->name,
                            'created_at' => $m->created_at?->toISOString(),
                        ])->values()->all(),
                ])->values()->all(),
            ],
        ];
    }
}
