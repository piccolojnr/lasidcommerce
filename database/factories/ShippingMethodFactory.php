<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShippingMethodFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name'              => ucwords($name),
            'code'              => strtoupper(Str::slug($name, '_')) . '_' . $this->faker->unique()->numberBetween(1, 9999),
            'method_type'       => 'delivery',
            'price_type'        => 'flat_rate',
            'flat_rate_amount'  => $this->faker->numberBetween(500, 5000),
            'min_delivery_days' => 1,
            'max_delivery_days' => 5,
            'description'       => null,
            'is_active'         => true,
        ];
    }
}
