<?php

namespace App\Domain\Catalog\Services;

use App\Models\Product;
use App\Models\StockItem;

class ProductStockResolver
{
    /**
     * @return array{quantity:int,status:string,is_backorderable:bool}
     */
    public function resolve(Product $product): array
    {
        $stockItems = $product->relationLoaded('stockItems')
            ? $product->stockItems
            : $product->stockItems()->get(['id', 'product_id', 'quantity_on_hand', 'quantity_reserved', 'reorder_level']);

        $quantity = (int) $stockItems->sum(fn (StockItem $stockItem) => $stockItem->availableQuantity());
        $reorderLevel = (int) $stockItems->sum('reorder_level');

        if (! $product->track_inventory) {
            return [
                'quantity' => $quantity,
                'status' => 'in_stock',
                'is_backorderable' => false,
            ];
        }

        if ($quantity <= 0) {
            return [
                'quantity' => $quantity,
                'status' => $product->allow_backorders ? 'in_stock' : 'out_of_stock',
                'is_backorderable' => $product->allow_backorders,
            ];
        }

        if ($quantity <= $reorderLevel) {
            return [
                'quantity' => $quantity,
                'status' => 'low_stock',
                'is_backorderable' => $product->allow_backorders,
            ];
        }

        return [
            'quantity' => $quantity,
            'status' => 'in_stock',
            'is_backorderable' => $product->allow_backorders,
        ];
    }
}
