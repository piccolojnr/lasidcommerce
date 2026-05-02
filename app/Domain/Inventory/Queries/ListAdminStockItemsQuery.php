<?php

namespace App\Domain\Inventory\Queries;

use App\Models\StockItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListAdminStockItemsQuery
{
    private ?string $search = null;
    private ?string $status = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search = $filters['search'] ?? null;
        $clone->status = $filters['status'] ?? null;

        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return StockItem::query()
            ->with(['product', 'productVariant'])
            ->when($this->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->whereHas('product', fn (Builder $productQuery) => $productQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%"))
                        ->orWhereHas('productVariant', fn (Builder $variantQuery) => $variantQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%"));
                });
            })
            ->when($this->status, function (Builder $query, string $status): void {
                match ($status) {
                    'out_of_stock' => $query->whereRaw('(quantity_on_hand - quantity_reserved) <= 0'),
                    'low_stock' => $query
                        ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
                        ->whereRaw('reorder_level is not null')
                        ->whereRaw('(quantity_on_hand - quantity_reserved) <= reorder_level'),
                    'in_stock' => $query->where(function (Builder $nestedQuery): void {
                        $nestedQuery
                            ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
                            ->where(function (Builder $levelQuery): void {
                                $levelQuery
                                    ->whereNull('reorder_level')
                                    ->orWhereRaw('(quantity_on_hand - quantity_reserved) > reorder_level');
                            });
                    }),
                    default => null,
                };
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
    }
}
