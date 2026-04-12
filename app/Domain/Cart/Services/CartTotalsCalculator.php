<?php

namespace App\Domain\Cart\Services;

use App\Models\Cart;

class CartTotalsCalculator
{
    public function recalculate(Cart $cart): void
    {
        $cart->loadMissing('cartItems');

        $subtotal = $cart->cartItems->sum('line_total');

        $cart->update([
            'subtotal_amount' => $subtotal,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'shipping_amount' => 0,
            'total_amount'    => $subtotal,
        ]);
    }
}
