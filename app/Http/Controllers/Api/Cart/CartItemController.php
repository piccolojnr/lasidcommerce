<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class CartItemController extends Controller
{
    public function store(StoreCartItemRequest $request): JsonResponse
    {
        return ApiResponse::created([], 'Cart item store placeholder');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        return ApiResponse::success([
            'id' => $cartItem->getKey(),
        ], 'Cart item update placeholder');
    }

    public function destroy(CartItem $cartItem): JsonResponse
    {
        return ApiResponse::success([
            'id' => $cartItem->getKey(),
        ], 'Cart item delete placeholder');
    }
}
