<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\ProductSlugGenerator;
use App\Domain\Inventory\Actions\CreateStockAdjustmentAction;
use App\Domain\Inventory\DTOs\StockAdjustmentData;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function __construct(
        private ProductSlugGenerator $slugGenerator,
        private SyncProductCollectionsAction $syncCollectionsAction,
        private CreateStockAdjustmentAction $stockAdjustmentAction,
    ) {}

    public function execute(array $attributes, ?User $actor = null): Product
    {
        $tagIds = $attributes['tag_ids'] ?? [];
        $collectionIds = $attributes['collection_ids'] ?? [];
        $initialQuantityOnHand = $this->nullableInteger($attributes['initial_quantity_on_hand'] ?? null);
        $initialReorderLevel = $this->nullableInteger($attributes['initial_reorder_level'] ?? null);
        $initialStockNote = $attributes['initial_stock_note'] ?? null;
        unset(
            $attributes['tag_ids'],
            $attributes['collection_ids'],
            $attributes['initial_quantity_on_hand'],
            $attributes['initial_reorder_level'],
            $attributes['initial_stock_note'],
        );

        if (empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name']);
        }

        return DB::transaction(function () use ($attributes, $tagIds, $collectionIds, $initialQuantityOnHand, $initialReorderLevel, $initialStockNote, $actor) {
            $product = Product::create($attributes);
            $product->tags()->sync($tagIds);
            $this->syncCollectionsAction->execute($product, $collectionIds);

            if ($product->track_inventory || $initialQuantityOnHand !== null || $initialReorderLevel !== null) {
                $stockItem = StockItem::query()->create([
                    'product_id' => $product->getKey(),
                    'product_variant_id' => null,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'reorder_level' => $initialReorderLevel,
                ]);

                if ($initialQuantityOnHand !== null && $initialQuantityOnHand > 0) {
                    $this->stockAdjustmentAction->execute($stockItem, new StockAdjustmentData(
                        type: StockMovement::TYPE_CORRECTION_ADD,
                        quantity: $initialQuantityOnHand,
                        referenceType: 'product_create',
                        referenceId: $product->getKey(),
                        note: $initialStockNote ?: 'Initial stock recorded during product creation.',
                        createdBy: $actor?->getKey(),
                    ));
                }
            }

            return $product->fresh(['tags', 'collections']);
        });
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
