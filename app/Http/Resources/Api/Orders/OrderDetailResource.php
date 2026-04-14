<?php

namespace App\Http\Resources\Api\Orders;

use App\Http\Resources\Api\Checkout\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $shippingAddress = null;

        if ($this->relationLoaded('orderAddresses')) {
            $shippingAddress = $this->orderAddresses->firstWhere('type', 'shipping');
        }

        return [
            'id'                   => $this->id,
            'order_number'         => $this->order_number,
            'status'               => $this->status,
            'payment_status'       => $this->payment_status,
            'fulfillment_status'   => $this->fulfillment_status,
            'currency_code'        => $this->currency_code,
            'subtotal_amount'      => $this->subtotal_amount,
            'discount_amount'      => $this->discount_amount,
            'tax_amount'           => $this->tax_amount,
            'shipping_amount'      => $this->shipping_amount,
            'total_amount'         => $this->total_amount,
            'shipping_zone_name'   => $this->shipping_zone_name,
            'shipping_method_name' => $this->shipping_method_name,
            'notes'                => $this->notes,
            'delivery_notes'       => $this->delivery_notes,
            'placed_at'            => $this->placed_at?->toISOString(),
            'items'                => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'shipping_address'     => $this->when(
                $shippingAddress !== null,
                fn () => $shippingAddress,
            ),
            'shipments'            => ShipmentTrackingResource::collection($this->whenLoaded('shipments')),
        ];
    }
}
