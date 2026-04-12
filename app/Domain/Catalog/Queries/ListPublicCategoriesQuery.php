<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class ListPublicCategoriesQuery
{
    private bool $withChildren = false;
    private bool $rootOnly = false;

    public function withChildren(): static
    {
        $clone = clone $this;
        $clone->withChildren = true;

        return $clone;
    }

    public function rootOnly(): static
    {
        $clone = clone $this;
        $clone->rootOnly = true;

        return $clone;
    }

    public function get(): Collection
    {
        return Category::active()
            ->with('media')
            ->when($this->rootOnly, fn ($q) => $q->whereNull('parent_id'))
            ->when($this->withChildren, fn ($q) => $q->with([
                'children' => fn ($q2) => $q2->active()->with('media')->orderBy('sort_order')->orderBy('name'),
            ]))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
