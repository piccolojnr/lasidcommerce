<?php

namespace App\Domain\Catalog\Services;

use App\Models\Collection;
use Illuminate\Support\Str;

class CollectionSlugGenerator
{
    public function generate(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : 'collection';
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Collection::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }
}
