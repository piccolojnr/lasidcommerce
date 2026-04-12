<?php

namespace App\Http\Resources\Api\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'sort_order'  => $this->sort_order,
            'image_url'   => $this->getFirstMediaUrl('images') ?: null,
            'children'    => $this->when(
                $this->relationLoaded('children'),
                fn () => static::collection($this->children),
            ),
        ];
    }
}
