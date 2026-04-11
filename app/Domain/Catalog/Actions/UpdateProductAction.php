<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\ProductSlugGenerator;
use App\Models\Product;

class UpdateProductAction
{
    public function __construct(
        private ProductSlugGenerator $slugGenerator,
    ) {}

    public function execute(Product $product, array $attributes): Product
    {
        if (isset($attributes['name']) && $attributes['name'] !== $product->name && empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name'], $product->id);
        }

        $product->update($attributes);

        return $product->fresh();
    }
}
