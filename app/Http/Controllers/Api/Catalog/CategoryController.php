<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Domain\Catalog\Queries\ListPublicCategoriesQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Catalog\CategoryResource;
use App\Models\Category;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct(
        private ListPublicCategoriesQuery $listQuery,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->listQuery->withChildren();

        if ($request->boolean('root_only')) {
            $query = $query->rootOnly();
        }

        return ApiResponse::success(CategoryResource::collection($query->get()));
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::active()
            ->with(['media', 'children' => fn ($q) => $q->active()->with('media')])
            ->where('slug', $slug)
            ->first();

        if ($category === null) {
            return ApiResponse::error('Category not found', [], Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::success(new CategoryResource($category));
    }
}
