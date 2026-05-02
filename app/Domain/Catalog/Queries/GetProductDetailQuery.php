<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GetProductDetailQuery
{
    public function findBySlug(string $slug): ?Product
    {
        return Product::with([
            'media',
            'category.media',
            'brand.media',
            'tags',
            'collections',
            'stockItems',
            'variants'   => fn ($q) => $q->where('is_active', true)->orderBy('price'),
            'optionTypes.optionValues',
        ])
            ->visibleOnStorefront()
            ->where('slug', $slug)
            ->first();
    }

    public function relatedProducts(Product $product, int $limit = 6): EloquentCollection
    {
        if ($product->category_id === null) {
            return new EloquentCollection();
        }

        return Product::with(['media', 'category', 'brand', 'tags', 'collections', 'stockItems'])
            ->visibleOnStorefront()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
