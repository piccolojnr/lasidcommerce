<?php

namespace App\Domain\Cart\Services;

class CartItemValidator
{
    public function validate(array $attributes): bool
    {
        return isset($attributes['product_id'], $attributes['quantity']);
    }
}
