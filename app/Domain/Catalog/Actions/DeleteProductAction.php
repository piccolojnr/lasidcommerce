<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\CannotDeleteProductException;
use App\Models\Product;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        if ($product->orderItems()->exists()) {
            throw new CannotDeleteProductException(
                'This product has been ordered. It cannot be deleted because order history must be preserved.'
            );
        }

        $product->delete();
    }
}
