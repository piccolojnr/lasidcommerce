<?php

namespace App\Domain\Order\Actions;

use App\Models\Order;

class CancelOrderAction
{
    public function execute(Order $order): Order
    {
        $order->status = 'cancelled';

        return $order;
    }
}
