<?php

namespace App\Domain\Shipping\Services;

use App\Domain\Shipping\DTOs\ShippingAddressData;
use App\Models\ShippingZone;

class ShippingZoneResolver
{
    public function resolve(ShippingAddressData $addressData): ?ShippingZone
    {
        $candidates = [
            ['district', $addressData->district],
            ['city',     $addressData->city ?: null],
            ['region',   $addressData->region],
            ['country',  $addressData->country],
        ];

        foreach ($candidates as [$type, $name]) {
            if (empty($name)) {
                continue;
            }

            $zone = ShippingZone::active()
                ->whereHas('areas', fn ($q) => $q->where('area_type', $type)->where('area_name', $name))
                ->first();

            if ($zone !== null) {
                return $zone;
            }
        }

        return null;
    }
}
