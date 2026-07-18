<?php

namespace App\Domain\Shipping\DTOs;

class ShippingAddressData
{
    public function __construct(
        public readonly string $country,
        public readonly ?string $region = null,
        public readonly string $city = '',
        public readonly ?string $district = null,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
