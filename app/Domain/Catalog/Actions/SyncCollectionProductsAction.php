<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Collection;

class SyncCollectionProductsAction
{
    /**
     * @param  array<int, array{product_id: int|string, sort_order?: int|string|null}>  $memberships
     */
    public function execute(Collection $collection, array $memberships): void
    {
        $syncData = [];

        foreach ($memberships as $membership) {
            $productId = (int) ($membership['product_id'] ?? 0);

            if ($productId < 1) {
                continue;
            }

            $syncData[$productId] = [
                'sort_order' => max(0, (int) ($membership['sort_order'] ?? 0)),
            ];
        }

        $collection->products()->sync($syncData);
    }
}
