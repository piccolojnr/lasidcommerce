<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ListPublicProductsQuery
{
    private ?string $search = null;
    private ?string $categorySlug = null;
    private ?string $brandSlug = null;
    private ?string $tagSlug = null;
    private ?string $collectionSlug = null;
    private bool $featuredOnly = false;
    private string $sort = 'latest';
    private ?int $minPrice = null;
    private ?int $maxPrice = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search       = $filters['search'] ?? null;
        $clone->categorySlug = $filters['category'] ?? null;
        $clone->brandSlug    = $filters['brand'] ?? null;
        $clone->tagSlug      = $filters['tag'] ?? null;
        $clone->collectionSlug = $filters['collection'] ?? null;
        $clone->featuredOnly = isset($filters['featured'])
            && filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN);
        $clone->sort         = in_array($filters['sort'] ?? '', ['price_asc', 'price_desc', 'latest'], true)
            ? ($filters['sort'])
            : 'latest';
        $clone->minPrice     = isset($filters['min_price']) ? (int) $filters['min_price'] : null;
        $clone->maxPrice     = isset($filters['max_price']) ? (int) $filters['max_price'] : null;

        return $clone;
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        $categoryIds = $this->categorySlug !== null
            ? $this->resolveCategoryIds($this->categorySlug)
            : null;
        $searchTerm = $this->search !== null
            ? '%'.mb_strtolower($this->search).'%'
            : null;

        return Product::with(['media', 'category', 'brand', 'tags', 'collections'])
            ->visibleOnStorefront()
            ->when($searchTerm, fn ($q, $term) => $q->whereRaw('LOWER(name) LIKE ?', [$term]))
            ->when($categoryIds !== null, fn ($q) => $q->whereHas(
                'category',
                fn ($q2) => $q2->whereIn('categories.id', $categoryIds),
            ))
            ->when($this->brandSlug, fn ($q, $slug) => $q->whereHas(
                'brand',
                fn ($q2) => $q2->where('slug', $slug),
            ))
            ->when($this->tagSlug, fn ($q, $slug) => $q->whereHas(
                'tags',
                fn ($q2) => $q2->where('slug', $slug)->where('is_active', true),
            ))
            ->when($this->collectionSlug, fn ($q, $slug) => $q->whereHas(
                'collections',
                fn ($q2) => $q2->where('slug', $slug)->where('is_active', true),
            ))
            ->when($this->featuredOnly, fn ($q) => $q->featured())
            ->when($this->minPrice !== null, fn ($q) => $q->where('base_price', '>=', $this->minPrice))
            ->when($this->maxPrice !== null, fn ($q) => $q->where('base_price', '<=', $this->maxPrice))
            ->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('base_price'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderByDesc('base_price'))
            ->when($this->sort !== 'price_asc' && $this->sort !== 'price_desc', fn ($q) => $q->orderByDesc('id'))
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<int>
     */
    private function resolveCategoryIds(string $slug): array
    {
        $rootCategory = Category::query()
            ->select(['id', 'parent_id', 'slug'])
            ->where('slug', $slug)
            ->first();

        if ($rootCategory === null) {
            return [];
        }

        $categories = Category::query()
            ->select(['id', 'parent_id'])
            ->get();

        $childrenByParent = $categories
            ->groupBy('parent_id')
            ->map(fn ($group) => $group->pluck('id')->all());

        $categoryIds = [];
        $stack = [$rootCategory->id];

        while ($stack !== []) {
            $currentId = array_pop($stack);

            if (in_array($currentId, $categoryIds, true)) {
                continue;
            }

            $categoryIds[] = $currentId;

            foreach ($childrenByParent->get($currentId, []) as $childId) {
                $stack[] = $childId;
            }
        }

        return $categoryIds;
    }
}
