<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\DTOs\OrderData;
use App\Models\Order;

class CreateOrderAction
{
    public function execute(OrderData $orderData): Order
    {
        return new Order($orderData->toArray());
    }
}
