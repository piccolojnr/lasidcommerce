<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Order::class);

        return Inertia::render('admin/orders/index');
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        return Inertia::render('admin/orders/show');
    }
}
