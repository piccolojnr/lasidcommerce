<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;

class StockReservationService
{
    /**
     * @throws InsufficientStockException
     */
    public function assertAvailable(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if (! $this->shouldReserve($product)) {
            return;
        }

        $stockItem = $this->stockItemFor($product, $variant);

        if ($stockItem === null || $stockItem->availableQuantity() < $quantity) {
            throw $this->insufficientStock($product, $variant, $stockItem?->availableQuantity() ?? 0);
        }
    }

    /**
     * @throws InsufficientStockException
     */
    public function reserveCart(Cart $cart): void
    {
        $cart->loadMissing('cartItems.product', 'cartItems.productVariant');

        foreach ($cart->cartItems as $item) {
            $product = $item->product;

            if ($product === null || ! $this->shouldReserve($product)) {
                continue;
            }

            $stockItem = $this->stockItemFor($product, $item->productVariant, lock: true);

            if ($stockItem === null || $stockItem->availableQuantity() < $item->quantity) {
                throw $this->insufficientStock($product, $item->productVariant, $stockItem?->availableQuantity() ?? 0);
            }

            $stockItem->quantity_reserved += $item->quantity;
            $stockItem->save();
        }
    }

    public function releaseOrder(Order $order): void
    {
        $this->applyOrderItems($order, function (StockItem $stockItem, OrderItem $item): void {
            $stockItem->quantity_reserved = max($stockItem->quantity_reserved - $item->quantity, 0);
            $stockItem->save();
        });
    }

    public function commitOrder(Order $order): void
    {
        $this->applyOrderItems($order, function (StockItem $stockItem, OrderItem $item): void {
            $reservedQuantity = min($stockItem->quantity_reserved, $item->quantity);

            if ($reservedQuantity < 1) {
                return;
            }

            $stockItem->quantity_reserved -= $reservedQuantity;
            $stockItem->quantity_on_hand = max($stockItem->quantity_on_hand - $reservedQuantity, 0);
            $stockItem->save();

            StockMovement::query()->create([
                'stock_item_id' => $stockItem->getKey(),
                'type' => StockMovement::TYPE_SALE,
                'quantity' => $reservedQuantity,
                'reference_type' => Order::class,
                'reference_id' => $item->order_id,
                'note' => 'Order completed.',
            ]);
        });
    }

    private function applyOrderItems(Order $order, callable $callback): void
    {
        $order->loadMissing('orderItems.product', 'orderItems.productVariant');

        foreach ($order->orderItems as $item) {
            $product = $item->product;

            if ($product === null || ! $this->shouldReserve($product)) {
                continue;
            }

            $stockItem = $this->stockItemFor($product, $item->productVariant, lock: true);

            if ($stockItem === null) {
                continue;
            }

            $callback($stockItem, $item);
        }
    }

    private function shouldReserve(Product $product): bool
    {
        return $product->track_inventory && ! $product->allow_backorders;
    }

    private function stockItemFor(Product $product, ?ProductVariant $variant, bool $lock = false): ?StockItem
    {
        $query = StockItem::query()
            ->when($lock, fn (Builder $query) => $query->lockForUpdate())
            ->where('product_id', $product->getKey())
            ->when(
                $variant !== null,
                fn (Builder $query) => $query->where('product_variant_id', $variant->getKey()),
                fn (Builder $query) => $query->whereNull('product_variant_id'),
            )
            ->orderBy('id');

        return $query->first();
    }

    private function insufficientStock(Product $product, ?ProductVariant $variant, int $availableQuantity): InsufficientStockException
    {
        $name = $variant?->name ?? $product->name;

        return new InsufficientStockException(
            "Only {$availableQuantity} unit(s) of \"{$name}\" are available."
        );
    }
}
