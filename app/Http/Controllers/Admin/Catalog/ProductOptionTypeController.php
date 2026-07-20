<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateProductOptionTypeAction;
use App\Domain\Catalog\Actions\DeleteProductOptionTypeAction;
use App\Domain\Catalog\Actions\UpdateProductOptionTypeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductOptionTypeRequest;
use App\Http\Requests\Admin\UpdateProductOptionTypeRequest;
use App\Models\Product;
use App\Models\ProductOptionType;
use Illuminate\Http\RedirectResponse;

class ProductOptionTypeController extends Controller
{
    public function __construct(
        private CreateProductOptionTypeAction $createAction,
        private UpdateProductOptionTypeAction $updateAction,
        private DeleteProductOptionTypeAction $deleteAction,
    ) {}

    public function index(Product $product): RedirectResponse
    {
        $this->authorize('view', $product);

        return redirect()->route('admin.catalog.products.variants.matrix', $product);
    }

    public function create(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        return redirect()->route('admin.catalog.products.variants.matrix', $product);
    }

    public function store(StoreProductOptionTypeRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $this->createAction->execute($product, $request->safe()->all());

        return back()->with('success', 'Option created successfully.');
    }

    public function show(ProductOptionType $optionType): RedirectResponse
    {
        $this->authorize('view', $optionType->product);

        return redirect()->route('admin.catalog.products.variants.matrix', $optionType->product);
    }

    public function edit(ProductOptionType $optionType): RedirectResponse
    {
        $this->authorize('update', $optionType->product);

        return redirect()->route('admin.catalog.products.variants.matrix', $optionType->product);
    }

    public function update(UpdateProductOptionTypeRequest $request, ProductOptionType $optionType): RedirectResponse
    {
        $this->authorize('update', $optionType->product);
        $this->updateAction->execute($optionType, $request->safe()->all());

        return back()->with('success', 'Option updated successfully.');
    }

    public function destroy(ProductOptionType $optionType): RedirectResponse
    {
        $this->authorize('delete', $optionType->product);
        $this->deleteAction->execute($optionType);

        return back()->with('success', 'Option deleted.');
    }
}
