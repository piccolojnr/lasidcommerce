<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminCollectionsQuery
{
    private ?string $search = null;
    private ?bool $isActive = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search = $filters['search'] ?? null;
        $clone->isActive = $filters['is_active'] ?? null;

        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return Collection::query()
            ->withCount('products')
            ->when($this->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($this->isActive !== null, fn ($q) => $q->where('is_active', $this->isActive))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
    }
}
