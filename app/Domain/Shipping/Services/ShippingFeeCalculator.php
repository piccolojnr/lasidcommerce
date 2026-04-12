<?php

namespace App\Domain\Shipping\Services;

use App\Models\ShippingMethod;

class ShippingFeeCalculator
{
    public function calculate(ShippingMethod $method): int
    {
        return $method->flat_rate_amount ?? 0;
    }
}
