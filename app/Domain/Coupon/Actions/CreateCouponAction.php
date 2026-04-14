<?php

namespace App\Domain\Coupon\Actions;

use App\Models\Coupon;

class CreateCouponAction
{
    public function execute(array $data): Coupon
    {
        return Coupon::query()->create($this->normalize($data));
    }

    private function normalize(array $data): array
    {
        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }
}
