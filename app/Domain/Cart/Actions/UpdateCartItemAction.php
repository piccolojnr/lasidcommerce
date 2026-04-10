<?php

namespace App\Domain\Cart\Actions;

use App\Models\CartItem;

class UpdateCartItemAction
{
    public function execute(CartItem $cartItem, array $attributes): CartItem
    {
        $cartItem->fill($attributes);

        return $cartItem;
    }
}
