<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\CategorySlugGenerator;
use App\Models\Category;

class CreateCategoryAction
{
    public function __construct(
        private CategorySlugGenerator $slugGenerator,
    ) {}

    public function execute(array $attributes): Category
    {
        if (empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name']);
        }

        return Category::create($attributes);
    }
}
