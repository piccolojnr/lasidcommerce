<?php

namespace App\Domain\Dashboard\Queries;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Support\Carbon;

class GetAdminDashboardDataQuery
{
    public function execute(): array
    {
        $since = now()->subDays(30);

        return [
            'overview' => [
                'revenue_last_30_days' => $this->revenueLast30Days($since),
                'orders_last_30_days' => $this->ordersLast30Days($since),
                'pending_fulfillment_orders' => $this->pendingFulfillmentOrders(),
                'low_stock_items' => $this->lowStockItems(),
                'active_coupons' => $this->activeCoupons(),
                'new_customers_last_30_days' => $this->newCustomersLast30Days($since),
            ],
            'recent_orders' => $this->recentOrders(),
            'recent_payments' => $this->recentPayments(),
            'recent_shipments' => $this->recentShipments(),
        ];
    }

    private function revenueLast30Days(Carbon $since): int
    {
        return (int) Payment::query()
            ->whereIn('status', ['paid', 'successful'])
            ->where('paid_at', '>=', $since)
            ->sum('amount');
    }

    private function ordersLast30Days(Carbon $since): int
    {
        return Order::query()
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', $since)
            ->count();
    }

    private function pendingFulfillmentOrders(): int
    {
        return Order::query()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('fulfillment_status', '!=', 'fulfilled')
            ->count();
    }

    private function lowStockItems(): int
    {
        return StockItem::query()
            ->whereRaw('(quantity_on_hand - quantity_reserved) <= reorder_level')
            ->count();
    }

    private function activeCoupons(): int
    {
        return Coupon::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->count();
    }

    private function newCustomersLast30Days(Carbon $since): int
    {
        return User::query()
            ->where('created_at', '>=', $since)
            ->count();
    }

    private function recentOrders(): array
    {
        return Order::query()
            ->latest('placed_at')
            ->limit(5)
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'email' => $order->email,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'fulfillment_status' => $order->fulfillment_status,
                'total_amount' => $order->total_amount,
                'currency_code' => $order->currency_code,
                'placed_at' => $order->placed_at?->toISOString(),
            ])
            ->all();
    }

    private function recentPayments(): array
    {
        return Payment::query()
            ->with('order:id,order_number')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'reference' => $payment->reference,
                'provider' => $payment->provider,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'currency_code' => $payment->currency_code,
                'order' => $payment->order ? [
                    'id' => $payment->order->id,
                    'order_number' => $payment->order->order_number,
                ] : null,
                'paid_at' => $payment->paid_at?->toISOString(),
                'created_at' => $payment->created_at?->toISOString(),
            ])
            ->all();
    }

    private function recentShipments(): array
    {
        return Shipment::query()
            ->with('order:id,order_number')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Shipment $shipment): array => [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'carrier_name' => $shipment->carrier_name,
                'status' => $shipment->status,
                'order' => $shipment->order ? [
                    'id' => $shipment->order->id,
                    'order_number' => $shipment->order->order_number,
                ] : null,
                'shipped_at' => $shipment->shipped_at?->toISOString(),
                'created_at' => $shipment->created_at?->toISOString(),
            ])
            ->all();
    }
}
