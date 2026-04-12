<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ListPublicProductsQuery
{
    private ?string $search = null;
    private ?string $categorySlug = null;
    private ?string $brandSlug = null;
    private bool $featuredOnly = false;
    private string $sort = 'latest';

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search       = $filters['search'] ?? null;
        $clone->categorySlug = $filters['category'] ?? null;
        $clone->brandSlug    = $filters['brand'] ?? null;
        $clone->featuredOnly = isset($filters['featured'])
            && filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN);
        $clone->sort         = in_array($filters['sort'] ?? '', ['price_asc', 'price_desc', 'latest'], true)
            ? ($filters['sort'])
            : 'latest';

        return $clone;
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Product::with(['media', 'category', 'brand'])
            ->active()
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->when($this->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($this->categorySlug, fn ($q, $slug) => $q->whereHas(
                'category',
                fn ($q2) => $q2->where('slug', $slug),
            ))
            ->when($this->brandSlug, fn ($q, $slug) => $q->whereHas(
                'brand',
                fn ($q2) => $q2->where('slug', $slug),
            ))
            ->when($this->featuredOnly, fn ($q) => $q->featured())
            ->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('base_price'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderByDesc('base_price'))
            ->when($this->sort !== 'price_asc' && $this->sort !== 'price_desc', fn ($q) => $q->orderByDesc('id'))
            ->paginate($perPage)
            ->withQueryString();
    }
}
