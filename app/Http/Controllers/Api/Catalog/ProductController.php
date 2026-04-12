<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Domain\Catalog\Queries\GetProductDetailQuery;
use App\Domain\Catalog\Queries\ListPublicProductsQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Catalog\ProductDetailResource;
use App\Http\Resources\Api\Catalog\ProductListResource;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(
        private ListPublicProductsQuery $listQuery,
        private GetProductDetailQuery $detailQuery,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search'   => $request->query('search') ?: null,
            'category' => $request->query('category') ?: null,
            'brand'    => $request->query('brand') ?: null,
            'featured' => $request->query('featured') ?: null,
            'sort'     => $request->query('sort') ?: 'latest',
        ];

        $paginator = $this->listQuery->withFilters($filters)->paginate(20);

        return ApiResponse::paginated(
            $paginator,
            ProductListResource::collection($paginator->items()),
        );
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->detailQuery->findBySlug($slug);

        if ($product === null) {
            return ApiResponse::error('Product not found', [], Response::HTTP_NOT_FOUND);
        }

        $related = $this->detailQuery->relatedProducts($product);

        return ApiResponse::success(
            (new ProductDetailResource($product))->withRelated($related),
        );
    }
}
