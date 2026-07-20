<?php

namespace App\Http\Resources\Api\Catalog;

use App\Domain\Catalog\Services\ProductBadgeService;
use App\Domain\Catalog\Services\ProductStockResolver;
use App\Http\Resources\Api\Catalog\Concerns\ResolvesProductImageUrls;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    use ResolvesProductImageUrls;

    private ?Collection $relatedProducts = null;

    public function withRelated(Collection $products): static
    {
        $this->relatedProducts = $products;

        return $this;
    }

    public function toArray(Request $request): array
    {
        // has_variants: true when the product has option types defined (i.e. is a variable product).
        // For these products the top-level stock is the aggregate of all variant stock items,
        // which is misleading on the storefront. Callers should use variants[].stock instead.
        $hasVariants = $this->relationLoaded('optionTypes') && $this->optionTypes->isNotEmpty();

        $stock = $hasVariants
            ? ['quantity' => null, 'status' => 'variant_dependent', 'is_backorderable' => $this->allow_backorders]
            : app(ProductStockResolver::class)->resolve($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'product_type' => $this->product_type,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'compare_at_price' => $this->compare_at_price,
            'is_featured' => $this->is_featured,
            'has_variants' => $hasVariants,
            'badges' => app(ProductBadgeService::class)->resolve($this->resource),
            'stock' => $stock,
            'track_inventory' => $this->track_inventory,
            'allow_backorders' => $this->allow_backorders,
            'published_at' => $this->published_at?->toISOString(),
            'images' => $this->getMedia('images')->map(
                fn ($media, $index) => $this->formatProductImage($media, $index)
            )->values(),
            'category' => $this->when(
                $this->category !== null,
                fn () => [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'image_url' => $this->category->getFirstMediaUrl('images') ?: null,
                ],
            ),
            'brand' => $this->when(
                $this->brand !== null,
                fn () => [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                    'slug' => $this->brand->slug,
                    'image_url' => $this->brand->getFirstMediaUrl('images') ?: null,
                ],
            ),
            'tags' => $this->when(
                $this->relationLoaded('tags'),
                fn () => $this->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'description' => $tag->description,
                ])->values(),
            ),
            'collections' => $this->when(
                $this->relationLoaded('collections'),
                fn () => $this->collections->map(fn ($collection) => [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                    'description' => $collection->description,
                    'sort_order' => (int) ($collection->pivot?->sort_order ?? 0),
                ])->values(),
            ),
            'option_types' => $this->when(
                $this->relationLoaded('optionTypes'),
                fn () => $this->optionTypes->map(fn ($optionType) => [
                    'id' => $optionType->id,
                    'name' => $optionType->name,
                    'values' => $optionType->relationLoaded('optionValues')
                        ? $optionType->optionValues->map(fn ($value) => [
                            'id' => $value->id,
                            'value' => $value->value,
                        ])->values()
                        : [],
                ])->values(),
            ),
            'variants' => $this->when(
                $this->relationLoaded('variants'),
                fn () => $this->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'compare_at_price' => $variant->compare_at_price,
                    'is_active' => $variant->is_active,
                    'option_value_ids' => $variant->relationLoaded('optionValues')
                        ? $variant->optionValues->pluck('id')->map(fn ($id) => (int) $id)->values()
                        : [],
                    'option_values' => $variant->relationLoaded('optionValues')
                        ? $variant->optionValues->map(fn ($value) => [
                            'id' => $value->id,
                            'value' => $value->value,
                            'option_type_id' => $value->option_type_id,
                            'option_type_name' => $value->optionType?->name,
                        ])->values()
                        : [],
                    'stock' => $this->variantStock($variant),
                ])->values(),
            ),
            'related_products' => ProductListResource::collection(
                $this->relatedProducts ?? new Collection,
            ),
        ];
    }

    private function variantStock($variant): array
    {
        $stockItems = $variant->relationLoaded('stockItems') ? $variant->stockItems : $variant->stockItems()->get();
        $quantity = (int) $stockItems->sum(fn ($stockItem) => $stockItem->availableQuantity());
        $reorderLevel = (int) $stockItems->sum('reorder_level');

        if (! $this->track_inventory) {
            return [
                'quantity' => $quantity,
                'status' => 'in_stock',
                'is_backorderable' => false,
            ];
        }

        if ($quantity <= 0) {
            return [
                'quantity' => $quantity,
                'status' => $this->allow_backorders ? 'in_stock' : 'out_of_stock',
                'is_backorderable' => $this->allow_backorders,
            ];
        }

        return [
            'quantity' => $quantity,
            'status' => $quantity <= $reorderLevel ? 'low_stock' : 'in_stock',
            'is_backorderable' => $this->allow_backorders,
        ];
    }
}
