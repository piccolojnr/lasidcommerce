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
                'shipping_zone_id' => $nationwideZone->getKey(),
                'area_type' => 'country',
                'area_name' => 'Ghana',
            ],
            []
        );

        ShippingMethod::query()->updateOrCreate(
            ['code' => 'same-day-accra'],
            [
                'shipping_zone_id' => $greaterAccraZone->getKey(),
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

        ShippingMethod::query()->updateOrCreate(
            ['code' => 'standard-ghana'],
            [
                'shipping_zone_id' => $nationwideZone->getKey(),
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
    }
}
