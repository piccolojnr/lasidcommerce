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

        // If the product has active variants, a variant must be selected.
        // We cannot add the base product directly — there is no unambiguous SKU or stock item.
        if ($variant === null && $product->variants()->active()->exists()) {
            throw new CartException('Please select a variant before adding this product to your cart.');
        }

        if ($variant !== null) {
            if ($variant->product_id !== $product->id) {
                throw new CartException('Variant does not belong to this product.');
            }

            if (! $variant->is_active) {
                throw new CartException('Product variant is not available.');
            }
        }
    }
}
