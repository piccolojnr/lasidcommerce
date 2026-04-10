<?php

namespace App\Domain\Order\Services;

use App\Models\Order;

class OrderStatusManager
{
    public function transition(Order $order, string $status): Order
    {
        $order->status = $status;

        return $order;
    }
}
