<?php

namespace App\Domain\Shipping\Actions;

use App\Domain\Shipping\DTOs\ShippingAddressData;
use App\Domain\Shipping\Services\ShippingFeeCalculator;
use App\Domain\Shipping\Services\ShippingZoneResolver;

class ResolveShippingMethodsAction
{
    public function __construct(
        private ShippingZoneResolver $zoneResolver,
        private ShippingFeeCalculator $feeCalculator,
    ) {}

    public function execute(ShippingAddressData $addressData): array
    {
        $zone = $this->zoneResolver->resolve($addressData);

        if ($zone === null) {
            return [
                'shipping_zone' => null,
                'shipping_methods' => [],
            ];
        }

        $zone->loadMissing(['shippingMethods' => fn ($query) => $query->active()->orderBy('name')]);

        return [
            'shipping_zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'code' => $zone->code,
            ],
            'shipping_methods' => $zone->shippingMethods
                ->map(fn ($method) => [
                    'id' => $method->id,
                    'name' => $method->name,
                    'code' => $method->code,
                    'method_type' => $method->method_type,
                    'price_type' => $method->price_type,
                    'flat_rate_amount' => $method->flat_rate_amount,
                    'shipping_amount' => $this->feeCalculator->calculate($method),
                    'min_delivery_days' => $method->min_delivery_days,
                    'max_delivery_days' => $method->max_delivery_days,
                    'description' => $method->description,
                ])
                ->values()
                ->all(),
        ];
    }
}
