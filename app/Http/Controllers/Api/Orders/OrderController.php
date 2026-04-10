<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success([], 'Customer orders placeholder');
    }

    public function show(Order $order): JsonResponse
    {
        return ApiResponse::success([
            'id' => $order->getKey(),
        ], 'Customer order detail placeholder');
    }
}
