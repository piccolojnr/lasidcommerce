<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\CannotDeleteCategoryException;
use App\Models\Category;

class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        // Note: children() excludes soft-deleted children (Eloquent default scope).
        // Soft-deleted children do not block deletion of the parent.
        if ($category->children()->exists()) {
            throw new CannotDeleteCategoryException(
                'This category has subcategories. Reassign or delete them first.'
            );
        }

        if ($category->products()->exists()) {
            throw new CannotDeleteCategoryException(
                'This category has products assigned to it. Reassign them first.'
            );
        }

        $category->delete();
    }
}
