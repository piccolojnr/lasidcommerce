<?php

namespace App\Domain\Cart\Actions;

use App\Models\Cart;
use App\Models\CartItem;

class AddCartItemAction
{
    public function execute(Cart $cart, array $attributes): CartItem
    {
        return new CartItem(array_merge($attributes, [
            'cart_id' => $cart->getKey(),
        ]));
    }
}
