<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success([], 'Category listing placeholder');
    }

    public function show(Category $category): JsonResponse
    {
        return ApiResponse::success([
            'id' => $category->getKey(),
        ], 'Category detail placeholder');
    }
}
