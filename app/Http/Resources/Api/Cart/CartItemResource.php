<?php

namespace App\Http\Resources\Api\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'product_id'            => $this->product_id,
            'product_variant_id'    => $this->product_variant_id,
            'product_name_snapshot' => $this->product_name_snapshot,
            'variant_name_snapshot' => $this->variant_name_snapshot,
            'sku_snapshot'          => $this->sku_snapshot,
            'unit_price'            => $this->unit_price,
            'quantity'              => $this->quantity,
            'line_total'            => $this->line_total,
        ];
    }
}
