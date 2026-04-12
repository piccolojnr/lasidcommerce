<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class GetProductDetailQuery
{
    public function findBySlug(string $slug): ?Product
    {
        return Product::with([
            'media',
            'category.media',
            'brand.media',
            'variants'   => fn ($q) => $q->where('is_active', true)->orderBy('price'),
            'optionTypes.optionValues',
        ])
            ->active()
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where('slug', $slug)
            ->first();
    }

    public function relatedProducts(Product $product, int $limit = 6): Collection
    {
        if ($product->category_id === null) {
            return new Collection();
        }

        return Product::with(['media'])
            ->active()
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
