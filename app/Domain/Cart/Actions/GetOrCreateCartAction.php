<?php

namespace App\Domain\Cart\Actions;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Str;

class GetOrCreateCartAction
{
    public function execute(?User $user = null, ?string $cartToken = null): Cart
    {
        if ($user !== null) {
            $cart = Cart::active()->where('user_id', $user->id)->latest()->first();

            return $cart ?? Cart::create([
                'user_id'         => $user->id,
                'status'          => 'active',
                'currency_code'   => 'GHS',
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'tax_amount'      => 0,
                'shipping_amount' => 0,
                'total_amount'    => 0,
            ]);
        }

        if ($cartToken !== null) {
            $cart = Cart::active()->where('session_id', $cartToken)->latest()->first();

            if ($cart !== null) {
                return $cart;
            }
        }

        return Cart::create([
            'session_id'      => (string) Str::uuid(),
            'status'          => 'active',
            'currency_code'   => 'GHS',
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'shipping_amount' => 0,
            'total_amount'    => 0,
        ]);
    }
}
