<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id'      => (string) Str::uuid(),
            'status'          => 'active',
            'currency_code'   => 'GHS',
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'shipping_amount' => 0,
            'total_amount'    => 0,
        ];
    }
}
