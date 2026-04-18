<?php

namespace App\Http\Resources\Api\Catalog\Concerns;

use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ResolvesProductImageUrls
{
    protected function formatProductImage(Media $media, int $index): array
    {
        $originalUrl = $media->getUrl();

        return [
            'id' => $media->id,
            'url' => $originalUrl,
            'thumb_url' => $this->safeProductConversionUrl($media, Product::IMAGE_CONVERSION_THUMB, $originalUrl),
            'card_url' => $this->safeProductConversionUrl($media, Product::IMAGE_CONVERSION_CARD, $originalUrl),
            'gallery_url' => $this->safeProductConversionUrl($media, Product::IMAGE_CONVERSION_GALLERY, $originalUrl),
            'is_primary' => $index === 0,
        ];
    }

    protected function safeProductConversionUrl(Media $media, string $conversionName, string $fallbackUrl): string
    {
        if (empty($media->conversions_disk)) {
            return $fallbackUrl;
        }

        try {
            return $media->getAvailableUrl([$conversionName]);
        } catch (\Throwable) {
            return $fallbackUrl;
        }
    }
}
