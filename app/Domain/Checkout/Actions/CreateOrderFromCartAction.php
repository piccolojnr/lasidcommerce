<?php

namespace App\Domain\Checkout\Actions;

use App\Models\Cart;
use App\Models\Order;

class CreateOrderFromCartAction
{
    public function execute(Cart $cart): Order
    {
        return new Order([
            'currency_code' => $cart->currency_code,
            'subtotal_amount' => $cart->subtotal_amount,
            'discount_amount' => $cart->discount_amount,
            'tax_amount' => $cart->tax_amount,
            'shipping_amount' => $cart->shipping_amount,
            'total_amount' => $cart->total_amount,
        ]);
    }
}
