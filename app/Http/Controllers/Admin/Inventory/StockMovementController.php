<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Domain\Inventory\Queries\GetAdminStockMovementDetailQuery;
use App\Domain\Inventory\Queries\ListAdminStockMovementsQuery;
use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class StockMovementController extends Controller
{
    public function __construct(
        private ListAdminStockMovementsQuery $listQuery,
        private GetAdminStockMovementDetailQuery $detailQuery,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', StockMovement::class);

        $filters = [
            'search' => $request->query('search') ?: null,
            'type' => $request->query('type') ?: null,
        ];

        $movements = $this->listQuery->withFilters($filters)->paginate();
        $movements->getCollection()->transform(fn (StockMovement $movement) => $this->formatMovement($movement));

        return Inertia::render('admin/inventory/stock-movements/index', [
            'stockMovements' => $movements,
            'filters' => $filters,
            'movementTypes' => StockMovement::adminAdjustmentTypes(),
        ]);
    }

    public function show(StockMovement $stockMovement): InertiaResponse
    {
        $this->authorize('view', $stockMovement);
        $stockMovement = $this->detailQuery->get($stockMovement);

        return Inertia::render('admin/inventory/stock-movements/show', [
            'stockMovement' => $this->formatMovement($stockMovement),
        ]);
    }

    private function formatMovement(StockMovement $stockMovement): array
    {
        return [
            'id' => $stockMovement->id,
            'stock_item_id' => $stockMovement->stock_item_id,
            'type' => $stockMovement->type,
            'quantity' => $stockMovement->quantity,
            'stock_delta' => StockMovement::stockDeltaForType($stockMovement->type, $stockMovement->quantity),
            'reference_type' => $stockMovement->reference_type,
            'reference_id' => $stockMovement->reference_id,
            'note' => $stockMovement->note,
            'creator_name' => $stockMovement->creator?->name,
            'product_name' => $stockMovement->stockItem?->product?->name,
            'product_sku' => $stockMovement->stockItem?->product?->sku,
            'variant_name' => $stockMovement->stockItem?->productVariant?->name,
            'variant_sku' => $stockMovement->stockItem?->productVariant?->sku,
            'created_at' => $stockMovement->created_at?->toISOString(),
        ];
    }
}
