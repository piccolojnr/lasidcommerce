<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\ProductSlugGenerator;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function __construct(
        private ProductSlugGenerator $slugGenerator,
        private SyncProductCollectionsAction $syncCollectionsAction,
    ) {}

    public function execute(array $attributes): Product
    {
        $tagIds = $attributes['tag_ids'] ?? [];
        $collectionIds = $attributes['collection_ids'] ?? [];
        unset($attributes['tag_ids'], $attributes['collection_ids']);

        if (empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name']);
        }

        return DB::transaction(function () use ($attributes, $tagIds, $collectionIds) {
            $product = Product::create($attributes);
            $product->tags()->sync($tagIds);
            $this->syncCollectionsAction->execute($product, $collectionIds);

            return $product->fresh(['tags', 'collections']);
        });
    }
}
