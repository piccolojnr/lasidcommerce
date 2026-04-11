<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;

class UpdateCategoryAction
{
    public function execute(Category $category, array $attributes): Category
    {
        $category->update($attributes);

        return $category->fresh();
    }
}
