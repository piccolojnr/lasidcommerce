<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\TagSlugGenerator;
use App\Models\Tag;

class CreateTagAction
{
    public function __construct(
        private TagSlugGenerator $slugGenerator,
    ) {}

    public function execute(array $attributes): Tag
    {
        if (empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name']);
        }

        return Tag::create($attributes);
    }
}
