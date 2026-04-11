<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminProductsQuery
{
    public function paginate(): LengthAwarePaginator
    {
        return Product::withCount('variants')
            ->with(['media', 'category', 'brand'])
            ->orderByDesc('id')
            ->paginate(20);
    }
}
