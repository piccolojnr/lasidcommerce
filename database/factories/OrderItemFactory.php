<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $unitPrice = $this->faker->numberBetween(500, 10000);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->words(3, true),
            'variant_name' => null,
            'sku' => strtoupper(Str::random(8)),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => $unitPrice * $quantity,
        ];
    }
}
