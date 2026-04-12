<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminBrandsQuery
{
    private ?string $search = null;
    private ?bool $isActive = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search   = $filters['search'] ?? null;
        $clone->isActive = isset($filters['is_active']) ? (bool) $filters['is_active'] : null;
        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return Brand::withCount('products')
            ->with('media')
            ->when($this->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($this->isActive !== null, fn ($q) => $q->where('is_active', $this->isActive))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
    }
}
