<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function show(): JsonResponse
    {
        return ApiResponse::success([], 'Cart placeholder');
    }
}
