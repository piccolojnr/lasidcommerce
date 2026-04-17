<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Tag;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminTagsQuery
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
        return Tag::query()
            ->withCount('products')
            ->when($this->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($this->isActive !== null, fn ($q) => $q->where('is_active', $this->isActive))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
    }
}
