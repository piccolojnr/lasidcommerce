<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderAddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'      => Order::factory(),
            'type'          => 'shipping',
            'name'          => $this->faker->name(),
            'phone'         => $this->faker->phoneNumber(),
            'country'       => 'Ghana',
            'region'        => 'Greater Accra',
            'city'          => 'Accra',
            'district'      => null,
            'address_line_1'=> $this->faker->streetAddress(),
            'address_line_2'=> null,
            'landmark'      => null,
            'postal_code'   => null,
        ];
    }
}
