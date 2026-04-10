<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Models\StockItem;
use Illuminate\Http\Response;

class StockAdjustmentController extends Controller
{
    public function store(AdjustStockRequest $request, StockItem $stockItem): Response
    {
        $this->authorize('update', $stockItem);

        return response("Admin stock adjustment placeholder: {$stockItem->getKey()}", Response::HTTP_CREATED);
    }
}
