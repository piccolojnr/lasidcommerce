<?php

namespace App\Http\Resources\Api\Catalog;

use App\Http\Resources\Api\Catalog\Concerns\ResolvesProductImageUrls;
use App\Domain\Catalog\Services\ProductBadgeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    use ResolvesProductImageUrls;

    public function toArray(Request $request): array
    {
        $primaryMedia = $this->getFirstMedia('images');
        $primaryImage = $primaryMedia !== null
            ? $this->formatProductImage($primaryMedia, 0)
            : null;

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'sku'               => $this->sku,
            'base_price'        => $this->base_price,
            'compare_at_price'  => $this->compare_at_price,
            'is_featured'       => $this->is_featured,
            'badges'            => app(ProductBadgeService::class)->resolve($this->resource),
            'primary_image_url' => $primaryImage['url'] ?? null,
            'primary_image_thumb_url' => $primaryImage['thumb_url'] ?? null,
            'primary_image_card_url' => $primaryImage['card_url'] ?? null,
            'primary_image_gallery_url' => $primaryImage['gallery_url'] ?? null,
            'category'          => $this->when(
                $this->relationLoaded('category') && $this->category !== null,
                fn () => [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ],
            ),
            'brand' => $this->when(
                $this->relationLoaded('brand') && $this->brand !== null,
                fn () => [
                    'id'   => $this->brand->id,
                    'name' => $this->brand->name,
                    'slug' => $this->brand->slug,
                ],
            ),
            'tags' => $this->when(
                $this->relationLoaded('tags'),
                fn () => $this->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->values(),
            ),
            'collections' => $this->when(
                $this->relationLoaded('collections'),
                fn () => $this->collections->map(fn ($collection) => [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                ])->values(),
            ),
        ];
    }
}
