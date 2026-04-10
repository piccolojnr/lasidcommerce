<?php

namespace App\Domain\Shipping\Services;

class ShippingFeeCalculator
{
    public function calculate(int $baseAmount = 0): int
    {
        return $baseAmount;
    }
}
