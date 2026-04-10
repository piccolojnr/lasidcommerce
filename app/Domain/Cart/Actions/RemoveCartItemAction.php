<?php

namespace App\Domain\Cart\Actions;

use App\Models\CartItem;

class RemoveCartItemAction
{
    public function execute(CartItem $cartItem): bool|null
    {
        return $cartItem->delete();
    }
}
