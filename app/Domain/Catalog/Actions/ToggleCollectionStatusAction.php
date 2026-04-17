<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Collection;

class ToggleCollectionStatusAction
{
    public function execute(Collection $collection): Collection
    {
        $collection->update([
            'is_active' => ! $collection->is_active,
        ]);

        return $collection->fresh();
    }
}
