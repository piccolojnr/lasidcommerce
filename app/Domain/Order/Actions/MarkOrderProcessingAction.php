<?php

namespace App\Domain\Order\Actions;

use App\Models\Order;

class MarkOrderProcessingAction
{
    public function execute(Order $order): Order
    {
        $order->status = 'processing';

        return $order;
    }
}
