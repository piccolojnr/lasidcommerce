<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingMethod;

class UpdateShippingMethodAction
{
    public function execute(ShippingMethod $method, array $attributes): ShippingMethod
    {
        $method->fill($attributes);

        return $method;
    }
}
