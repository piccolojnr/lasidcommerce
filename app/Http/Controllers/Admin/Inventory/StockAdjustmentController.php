<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Domain\Inventory\Actions\CreateStockAdjustmentAction;
use App\Domain\Inventory\DTOs\StockAdjustmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Models\StockItem;
use Illuminate\Http\RedirectResponse;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private CreateStockAdjustmentAction $createStockAdjustmentAction,
    ) {}

    public function store(AdjustStockRequest $request, StockItem $stockItem): RedirectResponse
    {
        $this->authorize('update', $stockItem);

        $this->createStockAdjustmentAction->execute(
            $stockItem,
            StockAdjustmentData::fromArray($request->validated(), $request->user()?->getKey()),
        );

        return back()->with('success', 'Stock adjustment recorded.');
    }
}
