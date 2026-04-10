<?php

namespace App\Domain\Coupon\Actions;

use App\Models\Coupon;

class ApplyCouponAction
{
    public function execute(string $code): ?Coupon
    {
        return Coupon::query()->where('code', $code)->first();
    }
}
