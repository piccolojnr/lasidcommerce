<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\ProductSlugGenerator;
use App\Models\Product;

class CreateProductAction
{
    public function __construct(
        private ProductSlugGenerator $slugGenerator,
    ) {}

    public function execute(array $attributes): Product
    {
        if (empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name']);
        }

        return Product::create($attributes);
    }
}
