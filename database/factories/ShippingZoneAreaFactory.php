<?php

namespace Database\Factories;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingZoneAreaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shipping_zone_id' => ShippingZone::factory(),
            'area_type' => 'country',
            'area_name' => 'Ghana',
        ];
    }
}
