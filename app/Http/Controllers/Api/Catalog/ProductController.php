<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success([], 'Product listing placeholder');
    }

    public function show(Product $product): JsonResponse
    {
        return ApiResponse::success([
            'id' => $product->getKey(),
        ], 'Product detail placeholder');
    }
}
