<?php

namespace App\Domain\Coupon\Services;

use App\Models\Coupon;

class CouponValidator
{
    public function validate(Coupon $coupon): bool
    {
        return $coupon->isCurrentlyValid();
    }
}
