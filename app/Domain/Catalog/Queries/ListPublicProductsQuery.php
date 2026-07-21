<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
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

    private string $popularityPeriod = '7d';

    private bool $onSaleOnly = false;

    private ?int $minPrice = null;

    private ?int $maxPrice = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;

        $clone->search = $filters['search'] ?? null;
        $clone->categorySlug = $filters['category'] ?? null;
        $clone->brandSlug = $filters['brand'] ?? null;
        $clone->tagSlug = $filters['tag'] ?? null;
        $clone->collectionSlug = $filters['collection'] ?? null;

        $clone->featuredOnly = isset($filters['featured'])
            && filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN);

        $clone->sort = in_array(
            $filters['sort'] ?? '',
            ['price_asc', 'price_desc', 'latest', 'popular'],
            true,
        )
            ? $filters['sort']
            : 'latest';

        $clone->popularityPeriod = in_array(
            $filters['popularity_period'] ?? '',
            ['7d', '30d', '365d', 'all'],
            true,
        )
            ? $filters['popularity_period']
            : '7d';

        $clone->onSaleOnly = isset($filters['on_sale'])
            && filter_var($filters['on_sale'], FILTER_VALIDATE_BOOLEAN);

        $clone->minPrice = isset($filters['min_price'])
            ? (int) $filters['min_price']
            : null;

        $clone->maxPrice = isset($filters['max_price'])
            ? (int) $filters['max_price']
            : null;

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

        $popularityFrom = $this->sort === 'popular'
            ? $this->popularityStartDate()
            : null;

        $salesScope = function (Builder $orderItems) use ($popularityFrom): void {
            $orderItems->whereHas('order', function (Builder $orders) use ($popularityFrom): void {
                $orders
                    ->where('payment_status', 'paid')
                    ->whereNull('cancelled_at')
                    ->when(
                        $popularityFrom !== null,
                        fn (Builder $query) => $query->where('paid_at', '>=', $popularityFrom),
                    );
            });
        };

        return Product::with([
            'media',
            'category',
            'brand',
            'tags',
            'collections',
            'stockItems',
        ])
            ->withCount('variants')
            ->visibleOnStorefront()
            ->when(
                $searchTerm,
                fn (Builder $query, string $term) => $query
                    ->whereRaw('LOWER(name) LIKE ?', [$term]),
            )
            ->when(
                $categoryIds !== null,
                fn (Builder $query) => $query->whereHas(
                    'category',
                    fn (Builder $categoryQuery) => $categoryQuery
                        ->whereIn('categories.id', $categoryIds),
                ),
            )
            ->when(
                $this->brandSlug,
                fn (Builder $query, string $slug) => $query->whereHas(
                    'brand',
                    fn (Builder $brandQuery) => $brandQuery->where('slug', $slug),
                ),
            )
            ->when(
                $this->tagSlug,
                fn (Builder $query, string $slug) => $query->whereHas(
                    'tags',
                    fn (Builder $tagQuery) => $tagQuery
                        ->where('slug', $slug)
                        ->where('is_active', true),
                ),
            )
            ->when(
                $this->collectionSlug,
                fn (Builder $query, string $slug) => $query->whereHas(
                    'collections',
                    fn (Builder $collectionQuery) => $collectionQuery
                        ->where('slug', $slug)
                        ->where('is_active', true),
                ),
            )
            ->when($this->featuredOnly, fn (Builder $query) => $query->featured())
            ->when(
                $this->minPrice !== null,
                fn (Builder $query) => $query->where('base_price', '>=', $this->minPrice),
            )
            ->when(
                $this->maxPrice !== null,
                fn (Builder $query) => $query->where('base_price', '<=', $this->maxPrice),
            )
            ->when(
                $this->sort === 'popular',
                function (Builder $query) use ($popularityFrom): void {
                    $uniqueOrderCountQuery = OrderItem::query()
                        ->selectRaw('COUNT(DISTINCT order_items.order_id)')
                        ->whereColumn('order_items.product_id', 'products.id');

                    $this->constrainToPaidOrders(
                        $uniqueOrderCountQuery,
                        $popularityFrom,
                    );

                    $query
                        ->addSelect([
                            'popularity_order_count' => $uniqueOrderCountQuery,
                        ])
                        ->withSum(
                            [
                                'orderItems as popularity_units_sold' => fn (Builder $orderItems) => $this
                                    ->constrainToPaidOrders(
                                        $orderItems,
                                        $popularityFrom,
                                    ),
                            ],
                            'quantity',
                        )
                        ->orderByDesc('popularity_order_count')
                        ->orderByDesc('popularity_units_sold')
                        ->orderByDesc('products.id');
                },
            )
            ->when(
                $this->onSaleOnly,
                fn (Builder $query) => $query->onSale(),
            )
            ->when(
                $this->sort === 'price_asc',
                fn (Builder $query) => $query->orderBy('base_price'),
            )
            ->when(
                $this->sort === 'price_desc',
                fn (Builder $query) => $query->orderByDesc('base_price'),
            )
            ->when(
                $this->sort === 'latest',
                fn (Builder $query) => $query->orderByDesc('id'),
            )
            ->paginate($perPage)
            ->withQueryString();
    }

    private function popularityStartDate(): ?CarbonInterface
    {
        return match ($this->popularityPeriod) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '365d' => now()->subDays(365),
            'all' => null,
        };
    }

    private function constrainToPaidOrders(
        Builder $orderItems,
        ?CarbonInterface $from,
    ): Builder {
        return $orderItems->whereHas(
            'order',
            function (Builder $orders) use ($from): void {
                $orders
                    ->paid()
                    ->notCancelled()
                    ->whereHas(
                        'payments',
                        function (Builder $payments) use ($from): void {
                            $payments
                                ->successful()
                                ->when(
                                    $from !== null,
                                    fn (Builder $query) => $query
                                        ->where('paid_at', '>=', $from),
                                );
                        },
                    );
            },
        );
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
