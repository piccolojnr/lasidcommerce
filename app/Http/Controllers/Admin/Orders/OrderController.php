<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Domain\Order\Queries\ListAdminOrdersQuery;
use App\Domain\Order\Services\OrderFulfillmentService;
use App\Domain\Order\Services\OrderStatusManager;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private ListAdminOrdersQuery $listQuery,
        private OrderStatusManager $statusManager,
        private OrderFulfillmentService $fulfillmentService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $filters = [
            'search' => $request->query('search') ?: null,
            'status' => $request->query('status') ?: null,
            'payment_status' => $request->query('payment_status') ?: null,
            'fulfillment_status' => $request->query('fulfillment_status') ?: null,
        ];

        $orders = $this->listQuery->withFilters($filters)->paginate();
        $orders->getCollection()->transform(fn (Order $order) => $this->formatOrderSummary($order));

        return Inertia::render('admin/orders/index', [
            'orders' => $orders,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load([
            'orderItems',
            'orderAddresses',
            'payments',
            'shipments.shipmentItems.orderItem',
            'shipments.warehouseLocation',
            'orderStatusHistories.changedBy:id,name',
        ]);

        $fulfillmentSummary = $this->fulfillmentService->summarize($order);

        return Inertia::render('admin/orders/show', [
            'order' => $this->formatOrderDetail($order, $fulfillmentSummary),
            'allowedStatuses' => $this->statusManager->allowedFrom($order->status),
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
            'canCreateShipment' => $request->user()?->can('create', Shipment::class) === true && $fulfillmentSummary['can_create_shipment'],
            'shipmentCreationMessage' => $this->shipmentCreationMessage($request, $order, $fulfillmentSummary),
        ]);
    }

    private function formatOrderSummary(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'email' => $order->email,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
            'currency_code' => $order->currency_code,
            'total_amount' => $order->total_amount,
            'placed_at' => $order->placed_at?->toISOString(),
        ];
    }

    private function formatOrderDetail(Order $order, array $fulfillmentSummary): array
    {
        $shippingAddress = $order->orderAddresses->firstWhere('type', 'shipping');
        $itemsById = collect($fulfillmentSummary['items'])->keyBy('id');

        return [
            ...$this->formatOrderSummary($order),
            'subtotal_amount' => $order->subtotal_amount,
            'discount_amount' => $order->discount_amount,
            'tax_amount' => $order->tax_amount,
            'shipping_amount' => $order->shipping_amount,
            'shipping_zone_name' => $order->shipping_zone_name,
            'shipping_method_name' => $order->shipping_method_name,
            'notes' => $order->notes,
            'delivery_notes' => $order->delivery_notes,
            'fulfillment_summary' => [
                'total_ordered_quantity' => $fulfillmentSummary['total_ordered_quantity'],
                'total_allocated_quantity' => $fulfillmentSummary['total_allocated_quantity'],
                'total_in_progress_quantity' => $fulfillmentSummary['total_in_progress_quantity'],
                'total_delivered_quantity' => $fulfillmentSummary['total_delivered_quantity'],
                'total_remaining_quantity' => $fulfillmentSummary['total_remaining_quantity'],
            ],
            'shipping_address' => $shippingAddress ? $this->formatAddress($shippingAddress) : null,
            'items' => $order->orderItems->map(fn (OrderItem $item) => $this->formatItem($item, $itemsById->get($item->id, [])))->values()->all(),
            'payments' => $order->payments->sortByDesc('id')->map(fn (Payment $payment) => $this->formatPayment($payment))->values()->all(),
            'shipments' => $order->shipments->sortByDesc('id')->map(fn (Shipment $shipment) => $this->formatShipment($shipment))->values()->all(),
            'history' => $order->orderStatusHistories->sortByDesc('created_at')->map(fn (OrderStatusHistory $history) => $this->formatHistory($history))->values()->all(),
        ];
    }

    private function formatAddress(OrderAddress $address): array
    {
        return [
            'type' => $address->type,
            'name' => $address->name,
            'phone' => $address->phone,
            'country' => $address->country,
            'region' => $address->region,
            'city' => $address->city,
            'district' => $address->district,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'landmark' => $address->landmark,
            'postal_code' => $address->postal_code,
        ];
    }

    private function formatItem(OrderItem $item, array $summary): array
    {
        return [
            'id' => $item->id,
            'product_name' => $item->product_name,
            'variant_name' => $item->variant_name,
            'sku' => $item->sku,
            'unit_price' => $item->unit_price,
            'quantity' => $item->quantity,
            'discount_amount' => $item->discount_amount,
            'tax_amount' => $item->tax_amount,
            'line_total' => $item->line_total,
            'allocated_quantity' => $summary['allocated_quantity'] ?? 0,
            'in_progress_quantity' => $summary['in_progress_quantity'] ?? 0,
            'delivered_quantity' => $summary['delivered_quantity'] ?? 0,
            'remaining_quantity' => $summary['remaining_quantity'] ?? $item->quantity,
        ];
    }

    private function formatPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'provider' => $payment->provider,
            'reference' => $payment->reference,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'currency_code' => $payment->currency_code,
            'paid_at' => $payment->paid_at?->toISOString(),
            'failed_at' => $payment->failed_at?->toISOString(),
        ];
    }

    private function formatShipment(Shipment $shipment): array
    {
        return [
            'id' => $shipment->id,
            'status' => $shipment->status,
            'carrier_name' => $shipment->carrier_name,
            'tracking_number' => $shipment->tracking_number,
            'tracking_url' => $shipment->tracking_url,
            'rider_name' => $shipment->rider_name,
            'rider_phone' => $shipment->rider_phone,
            'warehouse_location' => $shipment->warehouseLocation ? [
                'name' => $shipment->warehouseLocation->name,
                'code' => $shipment->warehouseLocation->code,
            ] : null,
            'packed_at' => $shipment->packed_at?->toISOString(),
            'shipped_at' => $shipment->shipped_at?->toISOString(),
            'delivered_at' => $shipment->delivered_at?->toISOString(),
            'failed_at' => $shipment->failed_at?->toISOString(),
            'returned_at' => $shipment->returned_at?->toISOString(),
            'items' => $shipment->shipmentItems->map(fn ($item) => [
                'order_item_id' => $item->order_item_id,
                'product_name' => $item->orderItem?->product_name,
                'sku' => $item->orderItem?->sku,
                'quantity' => $item->quantity,
            ])->values()->all(),
        ];
    }

    private function formatHistory(OrderStatusHistory $history): array
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

    private function shipmentCreationMessage(Request $request, Order $order, array $fulfillmentSummary): ?string
    {
        if ($request->user()?->can('create', Shipment::class) !== true) {
            return 'You do not have permission to create shipments.';
        }

        if ($order->status !== 'processing') {
            return 'Move this order to processing before creating shipments.';
        }

        if ($fulfillmentSummary['total_remaining_quantity'] === 0) {
            return 'All order quantities have already been assigned to shipments.';
        }

        return null;
    }
}
