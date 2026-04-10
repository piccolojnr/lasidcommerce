<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductOptionTypeRequest;
use App\Http\Requests\Admin\UpdateProductOptionTypeRequest;
use App\Models\Product;
use App\Models\ProductOptionType;
use Illuminate\Http\Response;

class ProductOptionTypeController extends Controller
{
    public function index(Product $product): Response
    {
        $this->authorize('view', $product);

        return response("Admin product option type index placeholder: {$product->getKey()}");
    }

    public function create(Product $product): Response
    {
        $this->authorize('update', $product);

        return response("Admin product option type create placeholder: {$product->getKey()}");
    }

    public function store(StoreProductOptionTypeRequest $request, Product $product): Response
    {
        $this->authorize('update', $product);

        return response("Admin product option type store placeholder: {$product->getKey()}", Response::HTTP_CREATED);
    }

    public function show(ProductOptionType $optionType): Response
    {
        $this->authorize('view', $optionType->product);

        return response("Admin product option type show placeholder: {$optionType->getKey()}");
    }

    public function edit(ProductOptionType $optionType): Response
    {
        $this->authorize('update', $optionType->product);

        return response("Admin product option type edit placeholder: {$optionType->getKey()}");
    }

    public function update(UpdateProductOptionTypeRequest $request, ProductOptionType $optionType): Response
    {
        $this->authorize('update', $optionType->product);

        return response("Admin product option type update placeholder: {$optionType->getKey()}");
    }

    public function destroy(ProductOptionType $optionType): Response
    {
        $this->authorize('delete', $optionType->product);

        return response()->noContent();
    }
}
