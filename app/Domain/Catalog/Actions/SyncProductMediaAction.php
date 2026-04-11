<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

class SyncProductMediaAction
{
    /**
     * @param array<int, UploadedFile> $newImages  New files to add to the images collection
     * @param array<int, int>          $removeImageIds  Media IDs to delete from the collection
     */
    public function execute(Product $product, array $newImages = [], array $removeImageIds = []): void
    {
        foreach ($removeImageIds as $mediaId) {
            $product->deleteMedia((int) $mediaId);
        }

        foreach ($newImages as $image) {
            $product->addMedia($image)->toMediaCollection('images');
        }
    }
}
