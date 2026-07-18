<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Exceptions\CartException;
use App\Domain\Cart\Services\CartItemValidator;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Services\StockReservationService;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;

class AddCartItemAction
{
    public function __construct(
        private CartItemValidator $validator,
        private StockReservationService $stockReservationService,
    ) {}

    public function execute(Cart $cart, int $productId, ?int $variantId, int $quantity): CartItem
    {
        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::findOrFail($variantId) : null;

        $this->validator->validate($product, $variant);

        $unitPrice = $variant?->price ?? $product->base_price;

        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->when(
                $variant !== null,
                fn ($q) => $q->where('product_variant_id', $variant->id),
                fn ($q) => $q->whereNull('product_variant_id'),
            )
            ->first();

        if ($existing !== null) {
            try {
                $this->stockReservationService->assertAvailable($product, $variant, $existing->quantity + $quantity);
            } catch (InsufficientStockException $e) {
                throw new CartException($e->getMessage());
            }

            $existing->quantity += $quantity;
            $existing->line_total = $existing->unit_price * $existing->quantity;
            $existing->save();

            return $existing;
        }

        try {
            $this->stockReservationService->assertAvailable($product, $variant, $quantity);
        } catch (InsufficientStockException $e) {
            throw new CartException($e->getMessage());
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'product_name_snapshot' => $product->name,
            'variant_name_snapshot' => $variant?->name,
            'sku_snapshot' => $variant?->sku ?? $product->sku,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $unitPrice * $quantity,
        ]);
    }
}
