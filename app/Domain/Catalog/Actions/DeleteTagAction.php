<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Tag;

class DeleteTagAction
{
    public function execute(Tag $tag): void
    {
        $tag->delete();
    }
}
