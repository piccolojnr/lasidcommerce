<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\SyncVariantOptionValuesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncProductVariantOptionValuesRequest;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;

class ProductVariantOptionValueController extends Controller
{
    public function __construct(
        private SyncVariantOptionValuesAction $syncAction,
    ) {}

    public function update(SyncProductVariantOptionValuesRequest $request, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $variant->product);
        $this->syncAction->execute($variant, $request->validated('option_value_ids'));

        return back()->with('success', 'Variant options updated.');
    }
}
