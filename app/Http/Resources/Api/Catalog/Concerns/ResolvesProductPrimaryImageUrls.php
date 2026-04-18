<?php

namespace App\Http\Resources\Api\Catalog\Concerns;

use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ResolvesProductPrimaryImageUrls
{
    protected function productPrimaryImagePayload(?Product $product): array
    {
        if ($product === null) {
            return $this->emptyPrimaryImagePayload();
        }

        $media = $product->getFirstMedia(Product::IMAGE_COLLECTION);

        if (! $media instanceof Media) {
            return $this->emptyPrimaryImagePayload();
        }

        $originalUrl = $media->getUrl();

        return [
            'primary_image_url' => $originalUrl,
            'primary_image_thumb_url' => $this->safePrimaryConversionUrl($media, Product::IMAGE_CONVERSION_THUMB, $originalUrl),
            'primary_image_card_url' => $this->safePrimaryConversionUrl($media, Product::IMAGE_CONVERSION_CARD, $originalUrl),
            'primary_image_gallery_url' => $this->safePrimaryConversionUrl($media, Product::IMAGE_CONVERSION_GALLERY, $originalUrl),
        ];
    }

    protected function emptyPrimaryImagePayload(): array
    {
        return [
            'primary_image_url' => null,
            'primary_image_thumb_url' => null,
            'primary_image_card_url' => null,
            'primary_image_gallery_url' => null,
        ];
    }

    protected function safePrimaryConversionUrl(Media $media, string $conversionName, string $fallbackUrl): string
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
