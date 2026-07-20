<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateProductOptionValueAction;
use App\Domain\Catalog\Actions\DeleteProductOptionValueAction;
use App\Domain\Catalog\Actions\UpdateProductOptionValueAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductOptionValueRequest;
use App\Http\Requests\Admin\UpdateProductOptionValueRequest;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use Illuminate\Http\RedirectResponse;

class ProductOptionValueController extends Controller
{
    public function __construct(
        private CreateProductOptionValueAction $createAction,
        private UpdateProductOptionValueAction $updateAction,
        private DeleteProductOptionValueAction $deleteAction,
    ) {}

    public function index(ProductOptionType $optionType): RedirectResponse
    {
        $this->authorize('view', $optionType->product);

        return redirect()->route('admin.catalog.products.variants.matrix', $optionType->product);
    }

    public function create(ProductOptionType $optionType): RedirectResponse
    {
        $this->authorize('update', $optionType->product);

        return redirect()->route('admin.catalog.products.variants.matrix', $optionType->product);
    }

    public function store(StoreProductOptionValueRequest $request, ProductOptionType $optionType): RedirectResponse
    {
        $this->authorize('update', $optionType->product);

        if ($request->has('values')) {
            foreach ($request->validated('values') as $value) {
                $this->createAction->execute($optionType, ['value' => $value]);
            }
        } else {
            $this->createAction->execute($optionType, $request->safe()->all());
        }

        return back()->with('success', 'Option value(s) created successfully.');
    }

    public function show(ProductOptionValue $value): RedirectResponse
    {
        $this->authorize('view', $value->optionType->product);

        return redirect()->route('admin.catalog.products.variants.matrix', $value->optionType->product);
    }

    public function edit(ProductOptionValue $value): RedirectResponse
    {
        $this->authorize('update', $value->optionType->product);

        return redirect()->route('admin.catalog.products.variants.matrix', $value->optionType->product);
    }

    public function update(UpdateProductOptionValueRequest $request, ProductOptionValue $value): RedirectResponse
    {
        $this->authorize('update', $value->optionType->product);
        $this->updateAction->execute($value, $request->safe()->all());

        return back()->with('success', 'Option value updated successfully.');
    }

    public function destroy(ProductOptionValue $value): RedirectResponse
    {
        $this->authorize('delete', $value->optionType->product);
        $this->deleteAction->execute($value);

        return back()->with('success', 'Option value deleted.');
    }
}
