<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;
use Illuminate\Http\UploadedFile;

class SyncCategoryMediaAction
{
    public function execute(Category $category, ?UploadedFile $image, bool $removeImage = false): void
    {
        if ($image !== null) {
            $category->clearMediaCollection('images');
            $category->addMedia($image)->toMediaCollection('images');
        } elseif ($removeImage) {
            $category->clearMediaCollection('images');
        }
    }
}
