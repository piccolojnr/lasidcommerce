<?php

namespace App\Http\Resources\Api\Checkout;

use App\Http\Resources\Api\Addresses\AddressResource;
use App\Http\Resources\Api\Cart\CartItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var array $this->resource */
        $data = $this->resource;

        $cart = $data['cart'];
        $address = $data['address'];
        $shippingZone = $data['shipping_zone'];
        $shippingMethod = $data['shipping_method'];

        return [
            'cart' => [
                'id' => $cart->id,
                'currency' => $cart->currency_code,
                'items' => CartItemResource::collection($cart->cartItems),
            ],
            'address' => new AddressResource($address),
            'shipping_zone' => [
                'id' => $shippingZone->id,
                'name' => $shippingZone->name,
                'code' => $shippingZone->code,
            ],
            'shipping_method' => [
                'id' => $shippingMethod->id,
                'name' => $shippingMethod->name,
                'code' => $shippingMethod->code,
                'min_delivery_days' => $shippingMethod->min_delivery_days,
                'max_delivery_days' => $shippingMethod->max_delivery_days,
            ],
            'totals' => [
                'subtotal_amount' => $data['subtotal_amount'],
                'discount_amount' => $data['discount_amount'],
                'tax_amount' => $data['tax_amount'],
                'shipping_amount' => $data['shipping_amount'],
                'total_amount' => $data['total_amount'],
            ],
        ];
    }
}
