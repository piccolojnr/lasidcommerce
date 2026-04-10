<?php

namespace App\Domain\Catalog\Services;

use Illuminate\Support\Str;

class ProductSlugGenerator
{
    public function generate(string $name): string
    {
        return Str::slug($name);
    }
}
