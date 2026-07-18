<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(3, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 9999),
            'sku' => strtoupper($this->faker->unique()->bothify('??-###')),
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => $this->faker->numberBetween(100, 100000),
            'short_description' => $this->faker->optional()->sentence(),
            'description' => $this->faker->optional()->paragraph(),
        ];
    }
}
