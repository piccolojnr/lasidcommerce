<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\CannotDeleteBrandException;
use App\Models\Brand;

class DeleteBrandAction
{
    public function execute(Brand $brand): void
    {
        if ($brand->products()->exists()) {
            throw new CannotDeleteBrandException(
                'This brand has products assigned to it. Reassign them first.'
            );
        }

        $brand->delete();
    }
}
