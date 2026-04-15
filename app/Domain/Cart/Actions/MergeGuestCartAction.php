<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Services\CartTotalsCalculator;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MergeGuestCartAction
{
    public function __construct(
        private CartTotalsCalculator $totalsCalculator,
    ) {}

    public function execute(User $user, ?string $cartToken): ?Cart
    {
        if ($cartToken === null || $cartToken === '') {
            return null;
        }

        return DB::transaction(function () use ($user, $cartToken): ?Cart {
            $guestCart = Cart::active()
                ->whereNull('user_id')
                ->where('session_id', $cartToken)
                ->with('cartItems')
                ->latest()
                ->lockForUpdate()
                ->first();

            if ($guestCart === null) {
                return null;
            }

            $userCart = Cart::active()
                ->where('user_id', $user->id)
                ->with('cartItems')
                ->latest()
                ->lockForUpdate()
                ->first();

            if ($userCart === null) {
                $guestCart->update([
                    'user_id' => $user->id,
                ]);

                $this->totalsCalculator->recalculate($guestCart->fresh('cartItems'));

                return $guestCart->fresh('cartItems');
            }

            foreach ($guestCart->cartItems as $guestItem) {
                $existingItem = $userCart->cartItems
                    ->first(fn ($item) => $item->product_id === $guestItem->product_id
                        && $item->product_variant_id === $guestItem->product_variant_id);

                if ($existingItem !== null) {
                    $existingItem->quantity += $guestItem->quantity;
                    $existingItem->line_total = $existingItem->unit_price * $existingItem->quantity;
                    $existingItem->save();

                    $guestItem->delete();

                    continue;
                }

                $guestItem->update([
                    'cart_id' => $userCart->id,
                ]);
            }

            $guestCart->update([
                'status' => 'merged',
            ]);

            $userCart->refresh()->load('cartItems');
            $this->totalsCalculator->recalculate($userCart);

            return $userCart->fresh('cartItems');
        });
    }
}
