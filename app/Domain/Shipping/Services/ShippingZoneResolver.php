<?php

namespace App\Domain\Shipping\Services;

use App\Domain\Shipping\DTOs\ShippingAddressData;
use App\Models\ShippingZone;

class ShippingZoneResolver
{
    public function resolve(ShippingAddressData $addressData): ?ShippingZone
    {
        return ShippingZone::active()
            ->whereHas('areas', function ($q) use ($addressData) {
                $q->where('area_type', 'country')
                    ->where('area_name', $addressData->country);
            })
            ->first();
    }
}
