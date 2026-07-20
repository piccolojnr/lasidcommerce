<?php

namespace App\Http\Resources\Api\Cart;

use App\Http\Resources\Api\Catalog\Concerns\ResolvesProductPrimaryImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    use ResolvesProductPrimaryImageUrls;

    public function toArray(Request $request): array
    {
        $product = $this->relationLoaded('product') ? $this->product : null;
        $variant = $this->relationLoaded('productVariant') ? $this->productVariant : null;

        // Resolve variant option values for the storefront cart UI.
        // e.g. [['option_type' => 'Color', 'value' => 'Black'], ['option_type' => 'Size', 'value' => 'M']]
        $optionValues = null;
        if ($variant !== null && $variant->relationLoaded('optionValues')) {
            $optionValues = $variant->optionValues->map(fn ($ov) => [
                'id'              => $ov->id,
                'value'           => $ov->value,
                'option_type_id'  => $ov->option_type_id,
                'option_type_name' => $ov->optionType?->name,
            ])->values()->all();
        }

        return [
            'id'                      => $this->id,
            'product_id'              => $this->product_id,
            'product_variant_id'      => $this->product_variant_id,
            'product_name_snapshot'   => $this->product_name_snapshot,
            'variant_name_snapshot'   => $this->variant_name_snapshot,
            'sku_snapshot'            => $this->sku_snapshot,
            'unit_price'              => $this->unit_price,
            'quantity'                => $this->quantity,
            'line_total'              => $this->line_total,
            'option_values'           => $optionValues,
            ...$this->productPrimaryImagePayload($product),
        ];
    }
}
