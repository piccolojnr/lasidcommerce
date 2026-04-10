<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Order::class);

        return response('Admin order index placeholder');
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        return response("Admin order show placeholder: {$order->getKey()}");
    }
}
