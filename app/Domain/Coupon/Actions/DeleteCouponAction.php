<?php

namespace App\Domain\Coupon\Actions;

use App\Models\Coupon;

class DeleteCouponAction
{
    public function execute(Coupon $coupon): void
    {
        $coupon->delete();
    }
}
