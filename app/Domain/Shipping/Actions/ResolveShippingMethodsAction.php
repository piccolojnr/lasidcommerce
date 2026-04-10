<?php

namespace App\Domain\Shipping\Actions;

use App\Domain\Shipping\DTOs\ShippingAddressData;

class ResolveShippingMethodsAction
{
    public function execute(ShippingAddressData $addressData): array
    {
        return $addressData->toArray();
    }
}
