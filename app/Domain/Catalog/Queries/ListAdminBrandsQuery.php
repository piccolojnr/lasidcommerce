<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminBrandsQuery
{
    public function paginate(): LengthAwarePaginator
    {
        return Brand::withCount('products')
            ->with('media')
            ->orderBy('name')
            ->paginate(20);
    }
}
