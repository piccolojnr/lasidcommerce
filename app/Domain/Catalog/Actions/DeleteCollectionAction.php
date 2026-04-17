<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Collection;

class DeleteCollectionAction
{
    public function execute(Collection $collection): void
    {
        $collection->delete();
    }
}
