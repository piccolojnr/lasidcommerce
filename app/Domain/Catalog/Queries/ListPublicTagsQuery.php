<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class ListPublicTagsQuery
{
    public function get(): Collection
    {
        return Tag::query()
            ->active()
            ->withCount([
                'products as products_count' => fn ($q) => $q->visibleOnStorefront(),
            ])
            ->orderBy('name')
            ->get();
    }
}
