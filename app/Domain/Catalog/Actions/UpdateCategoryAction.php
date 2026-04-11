<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\CategorySlugGenerator;
use App\Models\Category;

class UpdateCategoryAction
{
    public function __construct(
        private CategorySlugGenerator $slugGenerator,
    ) {}

    public function execute(Category $category, array $attributes): Category
    {
        if (isset($attributes['name']) && $attributes['name'] !== $category->name && empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name'], $category->id);
        }

        $category->update($attributes);

        return $category->fresh();
    }
}
