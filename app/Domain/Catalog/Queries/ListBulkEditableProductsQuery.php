<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ListBulkEditableProductsQuery
{
    /**
     * @return Collection<int, Product>
     */
    public function get(array $filters): Collection
    {
        return Product::withCount('variants')
            ->with(['media', 'category', 'brand', 'tags', 'collections', 'stockItems'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(
                fn ($nested) => $nested->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', (int) $id))
            ->when($filters['brand_id'] ?? null, fn ($query, $id) => $query->where('brand_id', (int) $id))
            ->when($filters['tag_id'] ?? null, fn ($query, $id) => $query->whereHas('tags', fn ($nested) => $nested->where('tags.id', (int) $id)))
            ->when($filters['collection_id'] ?? null, fn ($query, $id) => $query->whereHas('collections', fn ($nested) => $nested->where('collections.id', (int) $id)))
            ->orderBy('sku')
            ->limit(100)
            ->get();
    }
}
