<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Response;

class OrderStatusController extends Controller
{
    public function update(UpdateOrderStatusRequest $request, Order $order): Response
    {
        $this->authorize('update', $order);

        return response("Admin order status update placeholder: {$order->getKey()}");
    }
}
