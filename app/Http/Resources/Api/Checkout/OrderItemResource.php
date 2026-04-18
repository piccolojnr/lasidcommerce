<?php

namespace App\Http\Resources\Api\Checkout;

use App\Http\Resources\Api\Catalog\Concerns\ResolvesProductPrimaryImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    use ResolvesProductPrimaryImageUrls;

    public function toArray(Request $request): array
    {
        $product = $this->relationLoaded('product') ? $this->product : null;

        return [
            'id'                 => $this->id,
            'product_id'         => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product_name'       => $this->product_name,
            'variant_name'       => $this->variant_name,
            'sku'                => $this->sku,
            'unit_price'         => $this->unit_price,
            'quantity'           => $this->quantity,
            'discount_amount'    => $this->discount_amount,
            'tax_amount'         => $this->tax_amount,
            'line_total'         => $this->line_total,
            ...$this->productPrimaryImagePayload($product),
        ];
    }
}
