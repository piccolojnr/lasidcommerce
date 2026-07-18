<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Exceptions\CartException;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Services\StockReservationService;
use App\Models\CartItem;

class UpdateCartItemAction
{
    public function __construct(
        private StockReservationService $stockReservationService,
    ) {}

    public function execute(CartItem $cartItem, int $quantity): CartItem
    {
        $cartItem->loadMissing('product', 'productVariant');

        try {
            $this->stockReservationService->assertAvailable($cartItem->product, $cartItem->productVariant, $quantity);
        } catch (InsufficientStockException $e) {
            throw new CartException($e->getMessage());
        }

        $cartItem->quantity = $quantity;
        $cartItem->line_total = $cartItem->unit_price * $quantity;
        $cartItem->save();

        return $cartItem;
    }
}
