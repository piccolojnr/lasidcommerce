<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShippingZoneFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->country();

        return [
            'name' => $name,
            'code' => strtoupper(Str::slug($name, '_')),
            'country_code' => 'GH',
            'description' => null,
            'is_active' => true,
        ];
    }
}
