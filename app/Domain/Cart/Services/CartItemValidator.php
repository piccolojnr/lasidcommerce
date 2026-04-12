<?php

namespace App\Domain\Cart\Services;

use App\Domain\Cart\Exceptions\CartException;
use App\Models\Product;
use App\Models\ProductVariant;

class CartItemValidator
{
    public function validate(Product $product, ?ProductVariant $variant = null): void
    {
        if ($product->status !== 'active') {
            throw new CartException('Product is not available for purchase.');
        }

        if ($product->published_at !== null && $product->published_at->isFuture()) {
            throw new CartException('Product is not yet available for purchase.');
        }

        if ($variant !== null && $variant->product_id !== $product->id) {
            throw new CartException('Variant does not belong to this product.');
        }

        if ($variant !== null && ! $variant->is_active) {
            throw new CartException('Product variant is not available.');
        }
    }
}
