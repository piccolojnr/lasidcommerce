<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ListAdminProductsQuery
{
    public function execute(): Builder
    {
        return Product::query()->latest('id');
    }
}
