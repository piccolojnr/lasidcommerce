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

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search     = $filters['search'] ?? null;
        $clone->status     = $filters['status'] ?? null;
        $clone->categoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : null;
        $clone->brandId    = isset($filters['brand_id']) ? (int) $filters['brand_id'] : null;
        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return Product::withCount('variants')
            ->with(['media', 'category', 'brand'])
            ->when($this->search, fn ($q, $s) => $q->where(
                fn ($q2) => $q2->where('name', 'like', "%{$s}%")
                               ->orWhere('sku', 'like', "%{$s}%")
            ))
            ->when($this->status, fn ($q, $s) => $q->where('status', $s))
            ->when($this->categoryId, fn ($q, $id) => $q->where('category_id', $id))
            ->when($this->brandId, fn ($q, $id) => $q->where('brand_id', $id))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
    }
}
