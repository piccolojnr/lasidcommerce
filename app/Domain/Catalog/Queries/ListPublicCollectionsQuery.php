<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ListPublicCollectionsQuery
{
    public function get(): EloquentCollection
    {
        return Collection::query()
            ->active()
            ->withCount([
                'products as products_count' => fn ($q) => $q->visibleOnStorefront(),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
