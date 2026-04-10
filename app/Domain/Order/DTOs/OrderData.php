<?php

namespace App\Domain\Order\DTOs;

class OrderData
{
    public function __construct(
        public readonly ?int $userId = null,
        public readonly string $email = '',
        public readonly ?string $phone = null,
        public readonly string $currencyCode = 'GHS',
        public readonly int $subtotalAmount = 0,
        public readonly int $discountAmount = 0,
        public readonly int $taxAmount = 0,
        public readonly int $shippingAmount = 0,
        public readonly int $totalAmount = 0,
    ) {
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
            'phone' => $this->phone,
            'currency_code' => $this->currencyCode,
            'subtotal_amount' => $this->subtotalAmount,
            'discount_amount' => $this->discountAmount,
            'tax_amount' => $this->taxAmount,
            'shipping_amount' => $this->shippingAmount,
            'total_amount' => $this->totalAmount,
        ];
    }
}
