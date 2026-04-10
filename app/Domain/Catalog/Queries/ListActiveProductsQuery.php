<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ListActiveProductsQuery
{
    public function execute(): Builder
    {
        return Product::query()->active()->published();
    }
}
