<?php

namespace App\Domain\Shipping\Actions;

use App\Models\ShippingMethod;

class CreateShippingMethodAction
{
    public function execute(array $attributes): ShippingMethod
    {
        return ShippingMethod::query()->create($attributes);
    }
}
