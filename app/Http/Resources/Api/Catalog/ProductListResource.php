<?php

namespace App\Http\Resources\Api\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryMedia = $this->getFirstMedia('images');

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'sku'               => $this->sku,
            'base_price'        => $this->base_price,
            'compare_at_price'  => $this->compare_at_price,
            'is_featured'       => $this->is_featured,
            'primary_image_url' => $primaryMedia?->getUrl() ?? null,
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
        ];
    }
}
