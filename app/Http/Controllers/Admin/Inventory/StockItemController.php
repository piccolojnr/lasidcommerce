<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Domain\Inventory\Actions\UpdateStockItemAction;
use App\Domain\Inventory\Queries\GetAdminStockItemDetailQuery;
use App\Domain\Inventory\Queries\ListAdminStockItemsQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStockItemRequest;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class StockItemController extends Controller
{
    public function __construct(
        private ListAdminStockItemsQuery $listQuery,
        private GetAdminStockItemDetailQuery $detailQuery,
        private UpdateStockItemAction $updateStockItemAction,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', StockItem::class);

        $filters = [
            'search' => $request->query('search') ?: null,
            'status' => $request->query('status') ?: null,
        ];

        $stockItems = $this->listQuery->withFilters($filters)->paginate();
        $stockItems->getCollection()->transform(fn (StockItem $stockItem) => $this->formatStockItemSummary($stockItem));

        return Inertia::render('admin/inventory/stock-items/index', [
            'stockItems' => $stockItems,
            'filters' => $filters,
            'movementTypes' => StockMovement::adminAdjustmentTypes(),
        ]);
    }

    public function show(StockItem $stockItem): InertiaResponse
    {
        $this->authorize('view', $stockItem);
        $stockItem = $this->detailQuery->get($stockItem);

        return Inertia::render('admin/inventory/stock-items/show', [
            'stockItem' => $this->formatStockItemDetail($stockItem),
            'movementTypes' => StockMovement::adminAdjustmentTypes(),
        ]);
    }

    public function update(UpdateStockItemRequest $request, StockItem $stockItem): RedirectResponse
    {
        $this->authorize('update', $stockItem);
        $this->updateStockItemAction->execute($stockItem, $request->validated());

        return redirect()
            ->route('admin.inventory.stock-items.show', $stockItem)
            ->with('success', 'Stock item updated successfully.');
    }

    private function formatStockItemSummary(StockItem $stockItem): array
    {
        $availableQuantity = $stockItem->availableQuantity();
        $status = $this->resolveStockLevelStatus($stockItem);

        return [
            'id' => $stockItem->id,
            'product_id' => $stockItem->product_id,
            'product_name' => $stockItem->product?->name,
            'product_sku' => $stockItem->product?->sku,
            'product_variant_id' => $stockItem->product_variant_id,
            'variant_name' => $stockItem->productVariant?->name,
            'variant_sku' => $stockItem->productVariant?->sku,
            'quantity_on_hand' => $stockItem->quantity_on_hand,
            'quantity_reserved' => $stockItem->quantity_reserved,
            'available_quantity' => $availableQuantity,
            'reorder_level' => $stockItem->reorder_level,
            'status' => $status,
            'is_low_stock' => $status === 'low_stock',
            'updated_at' => $stockItem->updated_at?->toISOString(),
        ];
    }

    private function formatStockItemDetail(StockItem $stockItem): array
    {
        return [
            ...$this->formatStockItemSummary($stockItem),
            'movements' => $stockItem->stockMovements->map(fn (StockMovement $movement) => [
                'id' => $movement->id,
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'stock_delta' => StockMovement::stockDeltaForType($movement->type, $movement->quantity),
                'reference_type' => $movement->reference_type,
                'reference_id' => $movement->reference_id,
                'note' => $movement->note,
                'creator_name' => $movement->creator?->name,
                'created_at' => $movement->created_at?->toISOString(),
            ])->values()->all(),
        ];
    }

    private function resolveStockLevelStatus(StockItem $stockItem): string
    {
        $availableQuantity = $stockItem->availableQuantity();

        if ($availableQuantity <= 0) {
            return 'out_of_stock';
        }

        if ($stockItem->reorder_level !== null && $availableQuantity <= $stockItem->reorder_level) {
            return 'low_stock';
        }

        return 'in_stock';
    }
}
