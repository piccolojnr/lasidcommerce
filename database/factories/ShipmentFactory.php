<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status'   => 'pending',
        ];
    }

    public function packed(): static
    {
        return $this->state(['status' => 'packed', 'packed_at' => now()]);
    }

    public function shipped(): static
    {
        return $this->state(['status' => 'shipped', 'packed_at' => now()->subHour(), 'shipped_at' => now()]);
    }

    public function delivered(): static
    {
        return $this->state([
            'status'       => 'delivered',
            'packed_at'    => now()->subDay(),
            'shipped_at'   => now()->subHours(12),
            'delivered_at' => now(),
        ]);
    }
}
