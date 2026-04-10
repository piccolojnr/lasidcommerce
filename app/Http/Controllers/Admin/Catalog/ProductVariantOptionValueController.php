<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncProductVariantOptionValuesRequest;
use App\Models\ProductVariant;
use Illuminate\Http\Response;

class ProductVariantOptionValueController extends Controller
{
    public function update(SyncProductVariantOptionValuesRequest $request, ProductVariant $variant): Response
    {
        $this->authorize('update', $variant);

        return response("Admin product variant option values update placeholder: {$variant->getKey()}");
    }
}
