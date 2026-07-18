<?php

namespace App\Domain\Payment\DTOs;

class PaymentData
{
    public function __construct(
        public readonly ?int $orderId = null,
        public readonly ?int $userId = null,
        public readonly string $provider = 'paystack',
        public readonly int $amount = 0,
        public readonly string $currencyCode = 'GHS',
        public readonly ?string $reference = null,
    ) {}

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'provider' => $this->provider,
            'amount' => $this->amount,
            'currency_code' => $this->currencyCode,
            'reference' => $this->reference,
        ];
    }
}
