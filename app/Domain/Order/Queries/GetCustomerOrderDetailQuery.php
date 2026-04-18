<?php

namespace App\Domain\Order\Queries;

use App\Models\Order;

class GetCustomerOrderDetailQuery
{
    public function execute(Order $order): Order
    {
        return $order->load([
            'orderItems.product.media',
            'orderAddresses',
            'shipments',
        ]);
    }
}
