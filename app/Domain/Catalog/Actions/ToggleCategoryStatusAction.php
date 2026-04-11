<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;

class ToggleCategoryStatusAction
{
    public function execute(Category $category): Category
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category->fresh();
    }
}
