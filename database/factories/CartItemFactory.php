<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    public function definition(): array
    {
        $unitPrice = $this->faker->numberBetween(100, 50000);
        $quantity  = $this->faker->numberBetween(1, 5);

        return [
            'cart_id'               => Cart::factory(),
            'product_id'            => Product::factory(),
            'product_variant_id'    => null,
            'product_name_snapshot' => $this->faker->words(3, true),
            'variant_name_snapshot' => null,
            'sku_snapshot'          => strtoupper($this->faker->bothify('??-###')),
            'unit_price'            => $unitPrice,
            'quantity'              => $quantity,
            'line_total'            => $unitPrice * $quantity,
        ];
    }
}
