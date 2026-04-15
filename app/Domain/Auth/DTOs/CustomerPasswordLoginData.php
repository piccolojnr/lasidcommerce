<?php

namespace App\Domain\Auth\DTOs;

class CustomerPasswordLoginData
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $cartToken,
    ) {}

    public static function fromArray(array $attributes): self
    {
        return new self(
            email: mb_strtolower(trim($attributes['email'])),
            password: $attributes['password'],
            cartToken: $attributes['cart_token'] ?? null,
        );
    }
}
