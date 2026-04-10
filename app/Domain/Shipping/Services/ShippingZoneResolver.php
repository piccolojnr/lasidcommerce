<?php

namespace App\Domain\Shipping\Services;

use App\Domain\Shipping\DTOs\ShippingAddressData;

class ShippingZoneResolver
{
    public function resolve(ShippingAddressData $addressData): ?string
    {
        return $addressData->country;
    }
}
