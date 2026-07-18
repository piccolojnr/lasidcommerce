<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['shipping', 'billing']),
            'name' => $this->faker->name(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'country' => 'Ghana',
            'region' => $this->faker->optional()->word(),
            'city' => $this->faker->city(),
            'district' => null,
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => null,
            'landmark' => null,
            'postal_code' => null,
            'is_default' => false,
        ];
    }
}
