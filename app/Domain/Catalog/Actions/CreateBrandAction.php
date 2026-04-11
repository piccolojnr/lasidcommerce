<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\BrandSlugGenerator;
use App\Models\Brand;

class CreateBrandAction
{
    public function __construct(
        private BrandSlugGenerator $slugGenerator,
    ) {}

    public function execute(array $attributes): Brand
    {
        if (empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name']);
        }

        return Brand::create($attributes);
    }
}
