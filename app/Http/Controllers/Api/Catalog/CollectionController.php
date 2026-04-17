<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Domain\Catalog\Queries\ListPublicCollectionsQuery;
use App\Domain\Catalog\Queries\ListPublicProductsQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Catalog\CollectionResource;
use App\Http\Resources\Api\Catalog\ProductListResource;
use App\Models\Collection;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CollectionController extends Controller
{
    public function __construct(
        private ListPublicCollectionsQuery $listQuery,
        private ListPublicProductsQuery $productQuery,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(CollectionResource::collection($this->listQuery->get()));
    }

    public function show(string $slug): JsonResponse
    {
        $collection = Collection::query()
            ->active()
            ->withCount([
                'products as products_count' => fn ($q) => $q->visibleOnStorefront(),
            ])
            ->where('slug', $slug)
            ->first();

        if ($collection === null) {
            return ApiResponse::error('Collection not found', [], Response::HTTP_NOT_FOUND);
        }

        $paginator = $this->productQuery
            ->withFilters([
                'collection' => $slug,
                'sort' => request()->query('sort', 'latest'),
                'search' => request()->query('search'),
                'category' => request()->query('category'),
                'brand' => request()->query('brand'),
                'tag' => request()->query('tag'),
                'featured' => request()->query('featured'),
                'min_price' => request()->query('min_price'),
                'max_price' => request()->query('max_price'),
            ])
            ->paginate(20);

        return ApiResponse::success([
            'collection' => new CollectionResource($collection),
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
