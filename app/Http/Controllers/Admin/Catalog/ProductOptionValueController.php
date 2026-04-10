<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductOptionValueRequest;
use App\Http\Requests\Admin\UpdateProductOptionValueRequest;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use Illuminate\Http\Response;

class ProductOptionValueController extends Controller
{
    public function index(ProductOptionType $optionType): Response
    {
        $this->authorize('view', $optionType->product);

        return response("Admin product option value index placeholder: {$optionType->getKey()}");
    }

    public function create(ProductOptionType $optionType): Response
    {
        $this->authorize('update', $optionType->product);

        return response("Admin product option value create placeholder: {$optionType->getKey()}");
    }

    public function store(StoreProductOptionValueRequest $request, ProductOptionType $optionType): Response
    {
        $this->authorize('update', $optionType->product);

        return response("Admin product option value store placeholder: {$optionType->getKey()}", Response::HTTP_CREATED);
    }

    public function show(ProductOptionValue $value): Response
    {
        $this->authorize('view', $value->optionType->product);

        return response("Admin product option value show placeholder: {$value->getKey()}");
    }

    public function edit(ProductOptionValue $value): Response
    {
        $this->authorize('update', $value->optionType->product);

        return response("Admin product option value edit placeholder: {$value->getKey()}");
    }

    public function update(UpdateProductOptionValueRequest $request, ProductOptionValue $value): Response
    {
        $this->authorize('update', $value->optionType->product);

        return response("Admin product option value update placeholder: {$value->getKey()}");
    }

    public function destroy(ProductOptionValue $value): Response
    {
        $this->authorize('delete', $value->optionType->product);

        return response()->noContent();
    }
}
