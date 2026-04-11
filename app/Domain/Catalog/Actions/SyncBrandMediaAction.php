<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Brand;
use Illuminate\Http\UploadedFile;

class SyncBrandMediaAction
{
    public function execute(Brand $brand, ?UploadedFile $image, bool $removeImage = false): void
    {
        if ($image !== null) {
            $brand->clearMediaCollection('images');
            $brand->addMedia($image)->toMediaCollection('images');
        } elseif ($removeImage) {
            $brand->clearMediaCollection('images');
        }
    }
}
