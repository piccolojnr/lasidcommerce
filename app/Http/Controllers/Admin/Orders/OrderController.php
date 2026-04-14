<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Domain\Order\Queries\ListAdminOrdersQuery;
use App\Domain\Order\Services\OrderStatusManager;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private ListAdminOrdersQuery $listQuery,
        private OrderStatusManager $statusManager,
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

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load([
            'orderItems',
            'orderAddresses',
            'payments',
            'shipments',
            'orderStatusHistories.changedBy:id,name',
        ]);

        return Inertia::render('admin/orders/show', [
            'order' => $this->formatOrderDetail($order),
            'allowedStatuses' => $this->statusManager->allowedFrom($order->status),
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

    private function formatOrderDetail(Order $order): array
    {
        $shippingAddress = $order->orderAddresses->firstWhere('type', 'shipping');

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
            'shipping_address' => $shippingAddress ? $this->formatAddress($shippingAddress) : null,
            'items' => $order->orderItems->map(fn (OrderItem $item) => $this->formatItem($item))->values()->all(),
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

    private function formatItem(OrderItem $item): array
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
            'packed_at' => $shipment->packed_at?->toISOString(),
            'shipped_at' => $shipment->shipped_at?->toISOString(),
            'delivered_at' => $shipment->delivered_at?->toISOString(),
            'failed_at' => $shipment->failed_at?->toISOString(),
            'returned_at' => $shipment->returned_at?->toISOString(),
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
}
