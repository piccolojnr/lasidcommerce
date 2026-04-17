<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Domain\Catalog\Queries\ListPublicProductsQuery;
use App\Domain\Catalog\Queries\ListPublicTagsQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Catalog\ProductListResource;
use App\Http\Resources\Api\Catalog\TagResource;
use App\Models\Tag;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function __construct(
        private ListPublicTagsQuery $listQuery,
        private ListPublicProductsQuery $productQuery,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(TagResource::collection($this->listQuery->get()));
    }

    public function show(string $slug): JsonResponse
    {
        $tag = Tag::query()
            ->active()
            ->withCount([
                'products as products_count' => fn ($q) => $q->visibleOnStorefront(),
            ])
            ->where('slug', $slug)
            ->first();

        if ($tag === null) {
            return ApiResponse::error('Tag not found', [], Response::HTTP_NOT_FOUND);
        }

        $paginator = $this->productQuery
            ->withFilters([
                'tag' => $slug,
                'sort' => request()->query('sort', 'latest'),
                'search' => request()->query('search'),
                'category' => request()->query('category'),
                'brand' => request()->query('brand'),
                'collection' => request()->query('collection'),
                'featured' => request()->query('featured'),
                'min_price' => request()->query('min_price'),
                'max_price' => request()->query('max_price'),
            ])
            ->paginate(20);

        return ApiResponse::success([
            'tag' => new TagResource($tag),
            'products' => ProductListResource::collection($paginator->items()),
            'products_meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
