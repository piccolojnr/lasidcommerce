<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminProductsQuery
{
    private ?string $search = null;

    private ?string $status = null;

    private ?int $categoryId = null;

    private ?int $brandId = null;

    private ?int $tagId = null;

    private ?int $collectionId = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search = $filters['search'] ?? null;
        $clone->status = $filters['status'] ?? null;
        $clone->categoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : null;
        $clone->brandId = isset($filters['brand_id']) ? (int) $filters['brand_id'] : null;
        $clone->tagId = isset($filters['tag_id']) ? (int) $filters['tag_id'] : null;
        $clone->collectionId = isset($filters['collection_id']) ? (int) $filters['collection_id'] : null;

        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return Product::withCount('variants')
            ->with(['media', 'category', 'brand', 'tags', 'collections', 'stockItems'])
            ->when($this->search, fn ($q, $s) => $q->where(
                fn ($q2) => $q2->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
            ))
            ->when($this->status, fn ($q, $s) => $q->where('status', $s))
            ->when($this->categoryId, fn ($q, $id) => $q->where('category_id', $id))
            ->when($this->brandId, fn ($q, $id) => $q->where('brand_id', $id))
            ->when($this->tagId, fn ($q, $id) => $q->whereHas('tags', fn ($q2) => $q2->where('tags.id', $id)))
            ->when($this->collectionId, fn ($q, $id) => $q->whereHas('collections', fn ($q2) => $q2->where('collections.id', $id)))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
    }
}
