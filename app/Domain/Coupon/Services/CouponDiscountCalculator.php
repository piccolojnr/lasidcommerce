<?php

namespace App\Domain\Coupon\Services;

use App\Models\Coupon;

class CouponDiscountCalculator
{
    public function calculate(Coupon $coupon, int $subtotalAmount): int
    {
        return min($coupon->value, $subtotalAmount);
    }
}
