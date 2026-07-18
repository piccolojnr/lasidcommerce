<?php

namespace Database\Factories;

use App\Models\ProductOptionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'option_type_id' => ProductOptionType::factory(),
            'value' => ucwords($this->faker->unique()->word()),
        ];
    }
}
