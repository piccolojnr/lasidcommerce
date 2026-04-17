<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Services\TagSlugGenerator;
use App\Models\Tag;

class UpdateTagAction
{
    public function __construct(
        private TagSlugGenerator $slugGenerator,
    ) {}

    public function execute(Tag $tag, array $attributes): Tag
    {
        if (isset($attributes['name']) && $attributes['name'] !== $tag->name && empty($attributes['slug'])) {
            $attributes['slug'] = $this->slugGenerator->generate($attributes['name'], $tag->id);
        }

        $tag->update($attributes);

        return $tag->fresh();
    }
}
