<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(2, true));

        return [
            'product_id' => Product::factory(),
            'name' => $name,
            'sku' => strtoupper($this->faker->unique()->bothify('VAR-??-###')),
            'price' => $this->faker->numberBetween(1000, 50000),
            'compare_at_price' => null,
            'cost_price' => null,
            'barcode' => null,
            'weight' => null,
            'is_active' => true,
        ];
    }
}
