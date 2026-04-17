<?php

namespace App\Domain\Catalog\Services;

use App\Models\Tag;
use Illuminate\Support\Str;

class TagSlugGenerator
{
    public function generate(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : 'tag';
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Tag::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }
}
