<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Order\Services\OrderFulfillmentService;
use App\Domain\Shipment\Actions\CreateShipmentAction;
use App\Domain\Shipment\Actions\UpdateShipmentAction;
use App\Domain\Shipment\Exceptions\ShipmentException;
use App\Domain\Shipment\Queries\ListAdminShipmentsQuery;
use App\Domain\Shipment\Services\ShipmentStatusManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShipmentRequest;
use App\Http\Requests\Admin\UpdateShipmentRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\ShipmentStatusHistory;
use App\Models\ShippingMethod;
use App\Models\WarehouseLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShipmentController extends Controller
{
    public function __construct(
        private CreateShipmentAction $createShipmentAction,
        private UpdateShipmentAction $updateShipmentAction,
        private ListAdminShipmentsQuery $listQuery,
        private ShipmentStatusManager $statusManager,
        private OrderFulfillmentService $fulfillmentService,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Shipment::class);

        $filters = [
            'search' => $request->query('search') ?: null,
            'status' => $request->query('status') ?: null,
        ];

        $shipments = $this->listQuery->withFilters($filters)->paginate();
        $shipments->getCollection()->transform(fn (Shipment $shipment) => $this->formatShipmentSummary($shipment));

        return Inertia::render('admin/shipments/index', [
            'shipments' => $shipments,
            'filters' => $filters,
        ]);
    }

    public function show(Shipment $shipment): InertiaResponse
    {
        $this->authorize('view', $shipment);

        $shipment->load([
            'order:id,order_number,email,status,payment_status,fulfillment_status,total_amount,currency_code,placed_at',
            'shipmentItems.orderItem',
            'warehouseLocation',
            'shippingMethod',
            'statusHistories.changedBy:id,name',
        ]);

        $orderSummary = $shipment->order ? $this->fulfillmentService->summarize($shipment->order->loadMissing([
            'orderItems.shipmentItems.shipment',
            'shipments',
        ])) : null;

        return Inertia::render('admin/shipments/show', [
            'shipment' => $this->formatShipmentDetail($shipment, $orderSummary),
            'allowedStatuses' => $this->statusManager->allowedFrom($shipment->status),
            'availableWarehouses' => WarehouseLocation::query()
                ->active()
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'city', 'region', 'country', 'is_default'])
                ->map(fn (WarehouseLocation $warehouse) => [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                    'city' => $warehouse->city,
                    'region' => $warehouse->region,
                    'country' => $warehouse->country,
                    'is_default' => $warehouse->is_default,
                ])
                ->values()
                ->all(),
            'availableShippingMethods' => ShippingMethod::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'method_type'])
                ->map(fn (ShippingMethod $method) => [
                    'id' => $method->id,
                    'name' => $method->name,
                    'code' => $method->code,
                    'method_type' => $method->method_type,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Shipment::class);

        $order = Order::findOrFail($request->order_id);

        try {
            $shipment = $this->createShipmentAction->execute($order, $request->validated(), $request->user());
        } catch (ShipmentException $e) {
            return back()->withErrors(['order_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.shipments.show', $shipment)
            ->with('success', 'Shipment created successfully.');
    }

    public function quickStore(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('create', Shipment::class);

        $summary = $this->fulfillmentService->summarize($order);
        $items = collect($summary['items'])
            ->filter(fn (array $item) => $item['remaining_quantity'] > 0)
            ->map(fn (array $item) => [
                'order_item_id' => $item['id'],
                'quantity' => $item['remaining_quantity'],
            ])
            ->values()
            ->all();

        if ($items === []) {
            return back()->withErrors([
                'order_id' => 'There are no remaining shippable items for this order.',
            ]);
        }

        $defaultWarehouse = WarehouseLocation::query()
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->first();

        try {
            $shipment = $this->createShipmentAction->execute($order, [
                'warehouse_location_id' => $defaultWarehouse?->id,
                'items' => $items,
            ], $request->user());
        } catch (ShipmentException $e) {
            return back()->withErrors(['order_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.shipments.show', $shipment)
            ->with('success', 'Shipment created successfully.');
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->updateShipmentAction->execute($shipment, $request->validated());

        return redirect()
            ->route('admin.shipments.show', $shipment)
            ->with('success', 'Shipment details updated successfully.');
    }

    private function formatShipmentSummary(Shipment $shipment): array
    {
        return [
            'id' => $shipment->id,
            'status' => $shipment->status,
            'tracking_number' => $shipment->tracking_number,
            'carrier_name' => $shipment->carrier_name,
            'order' => $shipment->order ? [
                'id' => $shipment->order->id,
                'order_number' => $shipment->order->order_number,
                'email' => $shipment->order->email,
            ] : null,
            'packed_at' => $shipment->packed_at?->toISOString(),
            'shipped_at' => $shipment->shipped_at?->toISOString(),
            'delivered_at' => $shipment->delivered_at?->toISOString(),
            'failed_at' => $shipment->failed_at?->toISOString(),
            'returned_at' => $shipment->returned_at?->toISOString(),
        ];
    }

    private function formatShipmentDetail(Shipment $shipment, ?array $orderSummary = null): array
    {
        return [
            ...$this->formatShipmentSummary($shipment),
            'tracking_url' => $shipment->tracking_url,
            'rider_name' => $shipment->rider_name,
            'rider_phone' => $shipment->rider_phone,
            'notes' => $shipment->notes,
            'warehouse_location' => $shipment->warehouseLocation ? [
                'id' => $shipment->warehouseLocation->id,
                'name' => $shipment->warehouseLocation->name,
                'code' => $shipment->warehouseLocation->code,
                'city' => $shipment->warehouseLocation->city,
                'region' => $shipment->warehouseLocation->region,
                'country' => $shipment->warehouseLocation->country,
            ] : null,
            'shipping_method' => $shipment->shippingMethod ? [
                'id' => $shipment->shippingMethod->id,
                'name' => $shipment->shippingMethod->name,
                'code' => $shipment->shippingMethod->code,
                'method_type' => $shipment->shippingMethod->method_type,
            ] : null,
            'order_fulfillment_status' => $shipment->order?->fulfillment_status,
            'order_shipping_summary' => $orderSummary['shipping_summary'] ?? null,
            'order_remaining_quantity' => $orderSummary['total_remaining_quantity'] ?? 0,
            'can_reship_from_order' => $shipment->order !== null
                && $shipment->order->status === 'processing'
                && ($orderSummary['can_create_shipment'] ?? false),
            'items' => $shipment->shipmentItems
                ->map(fn (ShipmentItem $item) => [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'order_item_id' => $item->order_item_id,
                    'product_name' => $item->orderItem?->product_name,
                    'variant_name' => $item->orderItem?->variant_name,
                    'sku' => $item->orderItem?->sku,
                ])
                ->values()
                ->all(),
            'history' => $shipment->statusHistories
                ->sortByDesc('created_at')
                ->map(fn (ShipmentStatusHistory $history) => $this->formatHistory($history))
                ->values()
                ->all(),
        ];
    }

    private function formatHistory(ShipmentStatusHistory $history): array
    {
        return [
            'id' => $history->id,
            'from_status' => $history->from_status,
            'to_status' => $history->to_status,
            'note' => $history->note,
            'changed_by_name' => $history->changedBy?->name,
            'created_at' => $history->created_at?->toISOString(),
        ];
    }
}
