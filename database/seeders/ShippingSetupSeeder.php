<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;
use App\Models\WarehouseLocation;
use Illuminate\Database\Seeder;

class ShippingSetupSeeder extends Seeder
{
    public function run(): void
    {
        $greaterAccraZone = ShippingZone::query()->updateOrCreate(
            ['code' => 'GH-ACCRA'],
            [
                'name' => 'Greater Accra',
                'description' => 'Base delivery zone for Accra and nearby areas.',
                'country_code' => 'GH',
                'is_active' => true,
            ]
        );

        $ashantiZone = ShippingZone::query()->updateOrCreate(
            ['code' => 'GH-ASHANTI'],
            [
                'name' => 'Ashanti Region',
                'description' => 'Regional delivery coverage for Kumasi and nearby districts.',
                'country_code' => 'GH',
                'is_active' => true,
            ]
        );

        $northernZone = ShippingZone::query()->updateOrCreate(
            ['code' => 'GH-NORTHERN'],
            [
                'name' => 'Northern Corridor',
                'description' => 'Extended delivery coverage for Tamale and northern destinations.',
                'country_code' => 'GH',
                'is_active' => true,
            ]
        );

        $nationwideZone = ShippingZone::query()->updateOrCreate(
            ['code' => 'GH-NATIONWIDE'],
            [
                'name' => 'Nationwide Ghana',
                'description' => 'Fallback delivery zone for nationwide shipments.',
                'country_code' => 'GH',
                'is_active' => true,
            ]
        );

        ShippingZoneArea::query()->updateOrCreate(
            [
                'shipping_zone_id' => $greaterAccraZone->getKey(),
                'area_type' => 'region',
                'area_name' => 'Greater Accra',
            ],
            []
        );

        ShippingZoneArea::query()->updateOrCreate(
            [
                'shipping_zone_id' => $ashantiZone->getKey(),
                'area_type' => 'region',
                'area_name' => 'Ashanti',
            ],
            []
        );

        ShippingZoneArea::query()->updateOrCreate(
            [
                'shipping_zone_id' => $northernZone->getKey(),
                'area_type' => 'region',
                'area_name' => 'Northern',
            ],
            []
        );

        ShippingZoneArea::query()->updateOrCreate(
            [
                'shipping_zone_id' => $nationwideZone->getKey(),
                'area_type' => 'country',
                'area_name' => 'Ghana',
            ],
            []
        );

        $sameDayAccra = ShippingMethod::query()->updateOrCreate(
            ['code' => 'same-day-accra'],
            [
                'name' => 'Same Day Accra',
                'method_type' => 'courier',
                'price_type' => 'flat_rate',
                'flat_rate_amount' => 2500,
                'min_delivery_days' => 0,
                'max_delivery_days' => 1,
                'description' => 'Same-day delivery within Accra.',
                'is_active' => true,
            ]
        );

        $nextDayAccra = ShippingMethod::query()->updateOrCreate(
            ['code' => 'next-day-accra'],
            [
                'name' => 'Next Day Accra',
                'method_type' => 'courier',
                'price_type' => 'flat_rate',
                'flat_rate_amount' => 1800,
                'min_delivery_days' => 1,
                'max_delivery_days' => 2,
                'description' => 'Lower-cost next-day delivery in Greater Accra.',
                'is_active' => true,
            ]
        );

        $expressAshanti = ShippingMethod::query()->updateOrCreate(
            ['code' => 'express-ashanti'],
            [
                'name' => 'Ashanti Express',
                'method_type' => 'courier',
                'price_type' => 'flat_rate',
                'flat_rate_amount' => 4200,
                'min_delivery_days' => 1,
                'max_delivery_days' => 2,
                'description' => 'Fast tracked delivery within Kumasi and surrounding areas.',
                'is_active' => true,
            ]
        );

        $standardGhana = ShippingMethod::query()->updateOrCreate(
            ['code' => 'standard-ghana'],
            [
                'name' => 'Standard Ghana',
                'method_type' => 'courier',
                'price_type' => 'flat_rate',
                'flat_rate_amount' => 5000,
                'min_delivery_days' => 2,
                'max_delivery_days' => 5,
                'description' => 'Standard delivery across Ghana.',
                'is_active' => true,
            ]
        );

        $northernEconomy = ShippingMethod::query()->updateOrCreate(
            ['code' => 'northern-economy'],
            [
                'name' => 'Northern Economy',
                'method_type' => 'courier',
                'price_type' => 'flat_rate',
                'flat_rate_amount' => 6500,
                'min_delivery_days' => 3,
                'max_delivery_days' => 6,
                'description' => 'Longer-haul delivery for northern destinations.',
                'is_active' => true,
            ]
        );

        $pickup = ShippingMethod::query()->updateOrCreate(
            ['code' => 'pickup-station'],
            [
                'name' => 'Pickup Station',
                'method_type' => 'pickup',
                'price_type' => 'flat_rate',
                'flat_rate_amount' => 0,
                'min_delivery_days' => 0,
                'max_delivery_days' => 2,
                'description' => 'Pick up from a Lasid fulfillment point.',
                'is_active' => true,
            ]
        );

        $greaterAccraZone->shippingMethods()->syncWithoutDetaching([
            $sameDayAccra->getKey(),
            $nextDayAccra->getKey(),
            $pickup->getKey(),
        ]);
        $ashantiZone->shippingMethods()->syncWithoutDetaching([
            $expressAshanti->getKey(),
            $standardGhana->getKey(),
            $pickup->getKey(),
        ]);
        $northernZone->shippingMethods()->syncWithoutDetaching([
            $northernEconomy->getKey(),
            $standardGhana->getKey(),
        ]);
        $nationwideZone->shippingMethods()->syncWithoutDetaching([
            $standardGhana->getKey(),
            $pickup->getKey(),
        ]);

        WarehouseLocation::query()->updateOrCreate(
            ['code' => 'ACCRA-HQ'],
            [
                'name' => 'Accra Main Warehouse',
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'city' => 'Accra',
                'address_line_1' => 'Spintex Road',
                'address_line_2' => null,
                'phone' => '+233200000001',
                'email' => 'warehouse@example.com',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        WarehouseLocation::query()->updateOrCreate(
            ['code' => 'KUMASI-HUB'],
            [
                'name' => 'Kumasi Fulfillment Hub',
                'country' => 'Ghana',
                'region' => 'Ashanti',
                'city' => 'Kumasi',
                'address_line_1' => 'Asokwa Industrial Area',
                'address_line_2' => null,
                'phone' => '+233200000002',
                'email' => 'kumasi.warehouse@example.com',
                'is_active' => true,
                'is_default' => false,
            ]
        );

        WarehouseLocation::query()->updateOrCreate(
            ['code' => 'TAMALE-HUB'],
            [
                'name' => 'Tamale Dispatch Hub',
                'country' => 'Ghana',
                'region' => 'Northern',
                'city' => 'Tamale',
                'address_line_1' => 'Tamale Central Business District',
                'address_line_2' => null,
                'phone' => '+233200000003',
                'email' => 'tamale.warehouse@example.com',
                'is_active' => true,
                'is_default' => false,
            ]
        );
    }
}
