<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Brand;

class ToggleBrandStatusAction
{
    public function execute(Brand $brand): Brand
    {
        $brand->update(['is_active' => ! $brand->is_active]);

        return $brand->fresh();
    }
}
