<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Catalog\BrandResource;
use App\Models\Brand;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::query()
            ->active()
            ->with('media')
            ->withCount([
                'products as products_count' => fn ($query) => $query
                    ->active()
                    ->published(),
            ])
            ->orderBy('name')
            ->get();

        return ApiResponse::success(BrandResource::collection($brands));
    }

    public function show(string $slug): JsonResponse
    {
        $brand = Brand::query()
            ->active()
            ->with('media')
            ->withCount([
                'products as products_count' => fn ($query) => $query
                    ->active()
                    ->published(),
            ])
            ->where('slug', $slug)
            ->first();

        if ($brand === null) {
            return ApiResponse::error('Brand not found', [], Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::success(new BrandResource($brand));
    }
}
