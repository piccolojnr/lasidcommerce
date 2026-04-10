<?php

namespace App\Domain\Order\Actions;

use App\Models\Order;

class MarkOrderCompletedAction
{
    public function execute(Order $order): Order
    {
        $order->status = 'completed';

        return $order;
    }
}
