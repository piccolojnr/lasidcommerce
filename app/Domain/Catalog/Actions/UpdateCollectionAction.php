<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\CollectionSlugGenerator;
use App\Models\Collection;

class UpdateCollectionAction
{
    public function __construct(
        private CollectionSlugGenerator $slugGenerator,
        private SyncCollectionProductsAction $syncProductsAction,
    ) {}

    public function execute(Collection $collection, array $attributes): Collection
    {
        $productMemberships = $attributes['product_memberships'] ?? null;
        unset($attributes['product_memberships']);

        if (isset($attributes['name']) && $attributes['name'] !== $collection->name && empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name'], $collection->id);
        }

        $collection->update($attributes);

        if ($productMemberships !== null) {
            $this->syncProductsAction->execute($collection, $productMemberships);
        }

        return $collection->fresh(['products']);
    }
}
