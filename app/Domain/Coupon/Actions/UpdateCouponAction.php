<?php

namespace App\Domain\Coupon\Actions;

use App\Models\Coupon;

class UpdateCouponAction
{
    public function execute(Coupon $coupon, array $data): Coupon
    {
        $coupon->fill($this->normalize($data));
        $coupon->save();

        return $coupon->refresh();
    }

    private function normalize(array $data): array
    {
        if (array_key_exists('code', $data)) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
