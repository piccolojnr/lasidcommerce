<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingMethod;

class DeleteShippingMethodAction
{
    public function execute(ShippingMethod $method): ?bool
    {
        return $method->delete();
    }
}
