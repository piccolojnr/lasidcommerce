<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shipment_id' => Shipment::factory(),
            'from_status' => 'pending',
            'to_status' => 'packed',
            'note' => $this->faker->optional()->sentence(),
            'changed_by' => User::factory(),
        ];
    }
}
