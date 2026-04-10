<?php

namespace App\Domain\Cart\Services;

use App\Models\Cart;

class CartTotalsCalculator
{
    public function execute(Cart $cart): array
    {
        return [
            'subtotal_amount' => $cart->subtotal_amount,
            'discount_amount' => $cart->discount_amount,
            'tax_amount' => $cart->tax_amount,
            'shipping_amount' => $cart->shipping_amount,
            'total_amount' => $cart->total_amount,
        ];
    }
}
