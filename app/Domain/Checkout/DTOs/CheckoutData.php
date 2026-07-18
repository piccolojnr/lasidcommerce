<?php

namespace App\Domain\Checkout\DTOs;

class CheckoutData
{
    public function __construct(
        public readonly ?int $cartId = null,
        public readonly ?int $addressId = null,
        public readonly ?int $shippingMethodId = null,
        public readonly int $subtotalAmount = 0,
        public readonly int $discountAmount = 0,
        public readonly int $taxAmount = 0,
        public readonly int $shippingAmount = 0,
        public readonly int $totalAmount = 0,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
