<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SyncProductCollectionsAction
{
    /**
     * @param  array<int, int|string>  $collectionIds
     */
    public function execute(Product $product, array $collectionIds): void
    {
        $collectionIds = collect($collectionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        $existingSortOrders = $product->collections()
            ->pluck('collection_product.sort_order', 'collections.id')
            ->map(fn ($value) => (int) $value)
            ->all();

        $syncData = [];

        foreach ($collectionIds as $collectionId) {
            $syncData[$collectionId] = [
                'sort_order' => $existingSortOrders[$collectionId] ?? $this->nextSortOrder($collectionId),
            ];
        }

        $product->collections()->sync($syncData);
    }

    private function nextSortOrder(int $collectionId): int
    {
        $max = (int) DB::table('collection_product')
            ->where('collection_id', $collectionId)
            ->max('sort_order');

        return $max > 0 ? $max + 10 : 10;
    }
}
