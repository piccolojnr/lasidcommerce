<?php

namespace Tests\Unit\Domain\Dashboard;

use App\Domain\Dashboard\Queries\GetAdminDashboardDataQuery;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GetAdminDashboardDataQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_real_dashboard_overview_metrics(): void
    {
        $staffRole = Role::findOrCreate('support_agent', 'web');
        $recentOrderUser = User::factory()->create(['created_at' => now()->subDays(3)]);
        $oldOrderUser = User::factory()->create(['created_at' => now()->subDays(40)]);
        $completedOrderUser = User::factory()->create(['created_at' => now()->subDays(2)]);
        $successfulPaymentUser = User::factory()->create(['created_at' => now()->subDays(8)]);
        $oldPaymentUser = User::factory()->create(['created_at' => now()->subDays(60)]);
        $recentStaffUser = User::factory()->create(['created_at' => now()->subDays(4)]);
        $recentStaffUser->assignRole($staffRole);

        $recentOrder = Order::factory()->create([
            'user_id' => $recentOrderUser->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'total_amount' => 15000,
            'placed_at' => now()->subDays(3),
        ]);
        $oldOrder = Order::factory()->create([
            'user_id' => $oldOrderUser->id,
            'status' => 'processing',
            'placed_at' => now()->subDays(45),
        ]);
        Order::factory()->create([
            'user_id' => $completedOrderUser->id,
            'status' => 'completed',
            'fulfillment_status' => 'fulfilled',
            'placed_at' => now()->subDays(2),
        ]);
        $successfulPaymentOrder = Order::factory()->create([
            'user_id' => $successfulPaymentUser->id,
            'status' => 'completed',
            'fulfillment_status' => 'fulfilled',
            'placed_at' => now()->subDays(35),
        ]);
        $oldPendingPaymentOrder = Order::factory()->create([
            'user_id' => $oldPaymentUser->id,
            'status' => 'cancelled',
            'placed_at' => now()->subDays(35),
        ]);

        Payment::factory()->create([
            'order_id' => $recentOrder->id,
            'user_id' => $recentOrder->user_id,
            'status' => 'paid',
            'amount' => 15000,
            'paid_at' => now()->subDay(),
        ]);
        Payment::factory()->create([
            'order_id' => $successfulPaymentOrder->id,
            'user_id' => $successfulPaymentUser->id,
            'status' => 'successful',
            'amount' => 5000,
            'paid_at' => now()->subDays(10),
        ]);
        Payment::factory()->create([
            'order_id' => $oldOrder->id,
            'user_id' => $oldOrder->user_id,
            'status' => 'paid',
            'amount' => 9000,
            'paid_at' => now()->subDays(40),
        ]);
        Payment::factory()->create([
            'order_id' => $oldPendingPaymentOrder->id,
            'user_id' => $oldPaymentUser->id,
            'status' => 'pending',
            'amount' => 7000,
            'paid_at' => now()->subDay(),
        ]);

        $product = Product::factory()->create();
        StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_reserved' => 2,
            'reorder_level' => 3,
        ]);
        StockItem::query()->create([
            'product_id' => Product::factory()->create()->id,
            'quantity_on_hand' => 20,
            'quantity_reserved' => 1,
            'reorder_level' => 5,
        ]);

        Coupon::query()->create([
            'code' => 'ACTIVE10',
            'type' => 'fixed',
            'value' => 1000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'is_active' => true,
            'used_count' => 1,
            'usage_limit' => 5,
        ]);
        Coupon::query()->create([
            'code' => 'EXPIRED10',
            'type' => 'fixed',
            'value' => 1000,
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->subDay(),
            'is_active' => true,
            'used_count' => 0,
        ]);
        Coupon::query()->create([
            'code' => 'MAXED10',
            'type' => 'fixed',
            'value' => 1000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'is_active' => true,
            'used_count' => 3,
            'usage_limit' => 3,
        ]);

        User::factory()->create(['created_at' => now()->subDays(5)]);
        User::factory()->create(['created_at' => now()->subDays(50)]);

        $query = app(GetAdminDashboardDataQuery::class);
        $result = $query->execute();

        $this->assertSame(20000, $result['overview']['revenue_last_30_days']);
        $this->assertSame(2, $result['overview']['orders_last_30_days']);
        $this->assertSame(2, $result['overview']['pending_fulfillment_orders']);
        $this->assertSame(1, $result['overview']['low_stock_items']);
        $this->assertSame(1, $result['overview']['active_coupons']);
        $this->assertSame(4, $result['overview']['new_customers_last_30_days']);
    }

    public function test_it_returns_recent_activity_lists_in_descending_order(): void
    {
        $newestOrder = Order::factory()->create([
            'order_number' => 'ORD-NEWEST',
            'placed_at' => now()->subHour(),
        ]);
        $olderOrder = Order::factory()->create([
            'order_number' => 'ORD-OLDER',
            'placed_at' => now()->subDay(),
        ]);
        $paymentOrder = Order::factory()->create(['placed_at' => now()->subMonths(2)]);
        $olderPaymentOrder = Order::factory()->create(['placed_at' => now()->subMonths(2)->subDay()]);

        $newestPayment = Payment::factory()->create([
            'order_id' => $paymentOrder->id,
            'user_id' => $paymentOrder->user_id,
            'reference' => 'PAY-NEWEST',
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);
        $olderPayment = Payment::factory()->create([
            'order_id' => $olderPaymentOrder->id,
            'user_id' => $olderPaymentOrder->user_id,
            'reference' => 'PAY-OLDER',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        $shipmentOrder = Order::factory()->create(['placed_at' => now()->subMonths(2)]);
        $olderShipmentOrder = Order::factory()->create(['placed_at' => now()->subMonths(2)->subDay()]);

        $newestShipment = Shipment::factory()->create([
            'order_id' => $shipmentOrder->id,
            'tracking_number' => 'SHIP-NEWEST',
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);
        $olderShipment = Shipment::factory()->create([
            'order_id' => $olderShipmentOrder->id,
            'tracking_number' => 'SHIP-OLDER',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $query = app(GetAdminDashboardDataQuery::class);
        $result = $query->execute();

        $this->assertSame($newestOrder->id, $result['recent_orders'][0]['id']);
        $this->assertSame($olderOrder->id, $result['recent_orders'][1]['id']);
        $this->assertSame($newestPayment->id, $result['recent_payments'][0]['id']);
        $this->assertSame($olderPayment->id, $result['recent_payments'][1]['id']);
        $this->assertSame($newestShipment->id, $result['recent_shipments'][0]['id']);
        $this->assertSame($olderShipment->id, $result['recent_shipments'][1]['id']);
    }
}
