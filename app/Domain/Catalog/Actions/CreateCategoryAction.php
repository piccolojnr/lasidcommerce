<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;

class CreateCategoryAction
{
    public function execute(array $attributes): Category
    {
        return new Category($attributes);
    }
}
