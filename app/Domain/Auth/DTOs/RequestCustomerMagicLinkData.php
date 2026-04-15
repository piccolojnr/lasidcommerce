<?php

namespace App\Domain\Auth\DTOs;

class RequestCustomerMagicLinkData
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $cartToken,
        public readonly ?string $redirectTo,
    ) {}

    public static function fromArray(array $attributes): self
    {
        return new self(
            email: mb_strtolower(trim($attributes['email'])),
            cartToken: $attributes['cart_token'] ?? null,
            redirectTo: $attributes['redirect_to'] ?? null,
        );
    }
}
