<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\BrandSlugGenerator;
use App\Models\Brand;

class UpdateBrandAction
{
    public function __construct(
        private BrandSlugGenerator $slugGenerator,
    ) {}

    public function execute(Brand $brand, array $attributes): Brand
    {
        if (isset($attributes['name']) && $attributes['name'] !== $brand->name && empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name'], $brand->id);
        }

        $brand->update($attributes);

        return $brand->fresh();
    }
}
