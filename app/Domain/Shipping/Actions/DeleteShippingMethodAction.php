<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingMethod;

class DeleteShippingMethodAction
{
    public function execute(ShippingMethod $method): bool|null
    {
        return $method->delete();
    }
}
