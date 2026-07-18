<?php

namespace App\Domain\Inventory\Queries;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListAdminStockMovementsQuery
{
    private ?string $search = null;

    private ?string $type = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search = $filters['search'] ?? null;
        $clone->type = $filters['type'] ?? null;

        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with(['creator', 'stockItem.product', 'stockItem.productVariant'])
            ->when($this->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('note', 'like', "%{$search}%")
                        ->orWhere('reference_type', 'like', "%{$search}%")
                        ->orWhere('reference_id', 'like', "%{$search}%")
                        ->orWhereHas('stockItem.product', fn (Builder $productQuery) => $productQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%"))
                        ->orWhereHas('stockItem.productVariant', fn (Builder $variantQuery) => $variantQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%"));
                });
            })
            ->when($this->type, fn (Builder $query, string $type) => $query->where('type', $type))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();
    }
}
