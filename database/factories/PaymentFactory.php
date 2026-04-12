<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'      => Order::factory(),
            'user_id'       => User::factory(),
            'provider'      => 'paystack',
            'reference'     => 'PAY-' . strtoupper(Str::random(16)),
            'status'        => 'pending',
            'amount'        => 5000,
            'currency_code' => 'GHS',
        ];
    }
}
