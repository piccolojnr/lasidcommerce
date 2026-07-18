<?php

namespace App\Domain\Cart\Actions;

use App\Models\CartItem;

class UpdateCartItemAction
{
    public function execute(CartItem $cartItem, int $quantity): CartItem
    {
        $cartItem->quantity = $quantity;
        $cartItem->line_total = $cartItem->unit_price * $quantity;
        $cartItem->save();

        return $cartItem;
    }
}
