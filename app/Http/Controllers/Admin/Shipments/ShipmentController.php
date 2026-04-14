<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Shipment\Actions\CreateShipmentAction;
use App\Domain\Shipment\Exceptions\ShipmentException;
use App\Domain\Shipment\Queries\ListAdminShipmentsQuery;
use App\Domain\Shipment\Services\ShipmentStatusManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShipmentRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShipmentController extends Controller
{
    public function __construct(
        private CreateShipmentAction $createShipmentAction,
        private ListAdminShipmentsQuery $listQuery,
        private ShipmentStatusManager $statusManager,
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
        ]);

        return Inertia::render('admin/shipments/show', [
            'shipment' => $this->formatShipmentDetail($shipment),
            'allowedStatuses' => $this->statusManager->allowedFrom($shipment->status),
        ]);
    }

    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Shipment::class);

        $order = Order::findOrFail($request->order_id);

        try {
            $shipment = $this->createShipmentAction->execute($order, $request->validated());
        } catch (ShipmentException $e) {
            return back()->withErrors(['order_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.shipments.show', $shipment)
            ->with('success', 'Shipment created successfully.');
    }

    public function update(Shipment $shipment): \Illuminate\Http\Response
    {
        $this->authorize('update', $shipment);

        return response("Admin shipment update placeholder: {$shipment->getKey()}");
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

    private function formatShipmentDetail(Shipment $shipment): array
    {
        return [
            ...$this->formatShipmentSummary($shipment),
            'tracking_url' => $shipment->tracking_url,
            'rider_name' => $shipment->rider_name,
            'rider_phone' => $shipment->rider_phone,
            'notes' => $shipment->notes,
            'warehouse_location' => $shipment->warehouseLocation ? [
                'name' => $shipment->warehouseLocation->name,
                'code' => $shipment->warehouseLocation->code,
                'city' => $shipment->warehouseLocation->city,
                'region' => $shipment->warehouseLocation->region,
                'country' => $shipment->warehouseLocation->country,
            ] : null,
            'shipping_method' => $shipment->shippingMethod ? [
                'name' => $shipment->shippingMethod->name,
                'code' => $shipment->shippingMethod->code,
                'method_type' => $shipment->shippingMethod->method_type,
            ] : null,
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
        ];
    }
}
