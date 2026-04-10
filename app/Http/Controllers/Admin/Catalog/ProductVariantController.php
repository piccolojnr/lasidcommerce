<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Response;

class ProductVariantController extends Controller
{
    public function index(Product $product): Response
    {
        $this->authorize('view', $product);

        return response("Admin product variant index placeholder: {$product->getKey()}");
    }

    public function create(Product $product): Response
    {
        $this->authorize('update', $product);

        return response("Admin product variant create placeholder: {$product->getKey()}");
    }

    public function store(StoreProductVariantRequest $request, Product $product): Response
    {
        $this->authorize('update', $product);

        return response("Admin product variant store placeholder: {$product->getKey()}", Response::HTTP_CREATED);
    }

    public function show(ProductVariant $variant): Response
    {
        $this->authorize('view', $variant->product);

        return response("Admin product variant show placeholder: {$variant->getKey()}");
    }

    public function edit(ProductVariant $variant): Response
    {
        $this->authorize('update', $variant->product);

        return response("Admin product variant edit placeholder: {$variant->getKey()}");
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $variant): Response
    {
        $this->authorize('update', $variant->product);

        return response("Admin product variant update placeholder: {$variant->getKey()}");
    }

    public function destroy(ProductVariant $variant): Response
    {
        $this->authorize('delete', $variant->product);

        return response()->noContent();
    }
}
