<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\CollectionSlugGenerator;
use App\Models\Collection;

class CreateCollectionAction
{
    public function __construct(
        private CollectionSlugGenerator $slugGenerator,
        private SyncCollectionProductsAction $syncProductsAction,
    ) {}

    public function execute(array $attributes): Collection
    {
        $productMemberships = $attributes['product_memberships'] ?? [];
        unset($attributes['product_memberships']);

        if (empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name']);
        }

        $collection = Collection::create($attributes);
        $this->syncProductsAction->execute($collection, $productMemberships);

        return $collection->fresh(['products']);
    }
}
