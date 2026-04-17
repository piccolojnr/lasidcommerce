<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Tag;

class ToggleTagStatusAction
{
    public function execute(Tag $tag): Tag
    {
        $tag->update([
            'is_active' => ! $tag->is_active,
        ]);

        return $tag->fresh();
    }
}
