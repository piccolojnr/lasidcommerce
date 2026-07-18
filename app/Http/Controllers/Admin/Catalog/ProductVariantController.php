<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateProductVariantAction;
use App\Domain\Catalog\Actions\DeleteProductVariantAction;
use App\Domain\Catalog\Actions\UpdateProductVariantAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;

class ProductVariantController extends Controller
{
    public function __construct(
        private CreateProductVariantAction $createAction,
        private UpdateProductVariantAction $updateAction,
        private DeleteProductVariantAction $deleteAction,
    ) {}

    public function index(Product $product): RedirectResponse
    {
        $this->authorize('view', $product);

        return redirect()->route('admin.catalog.products.edit', $product);
    }

    public function create(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        return redirect()->route('admin.catalog.products.edit', $product);
    }

    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $this->createAction->execute($product, $request->safe()->all());

        return back()->with('success', 'Variant created successfully.');
    }

    public function show(ProductVariant $variant): RedirectResponse
    {
        $this->authorize('view', $variant->product);

        return redirect()->route('admin.catalog.products.show', $variant->product);
    }

    public function edit(ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $variant->product);

        return redirect()->route('admin.catalog.products.edit', $variant->product);
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $variant->product);
        $this->updateAction->execute($variant, $request->safe()->all());

        return back()->with('success', 'Variant updated successfully.');
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $this->authorize('delete', $variant->product);
        $this->deleteAction->execute($variant);

        return back()->with('success', 'Variant deleted.');
    }
}
