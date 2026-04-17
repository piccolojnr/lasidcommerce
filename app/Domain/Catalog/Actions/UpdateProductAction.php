<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\ProductSlugGenerator;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateProductAction
{
    public function __construct(
        private ProductSlugGenerator $slugGenerator,
        private SyncProductCollectionsAction $syncCollectionsAction,
    ) {}

    public function execute(Product $product, array $attributes): Product
    {
        $tagIds = $attributes['tag_ids'] ?? null;
        $collectionIds = $attributes['collection_ids'] ?? null;
        unset($attributes['tag_ids'], $attributes['collection_ids']);

        if (isset($attributes['name']) && $attributes['name'] !== $product->name && empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name'], $product->id);
        }

        return DB::transaction(function () use ($product, $attributes, $tagIds, $collectionIds) {
            $product->update($attributes);

            if ($tagIds !== null) {
                $product->tags()->sync($tagIds);
            }

            if ($collectionIds !== null) {
                $this->syncCollectionsAction->execute($product, $collectionIds);
            }

            return $product->fresh(['tags', 'collections']);
        });
    }
}
