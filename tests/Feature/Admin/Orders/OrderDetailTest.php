<?php

namespace Tests\Feature\Admin\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage orders', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage shipments', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(['manage orders', 'manage shipments']);
    }

    public function test_order_show_includes_shipment_creation_context(): void
    {
        $order = Order::factory()->create([
            'status' => 'processing',
            'fulfillment_status' => 'unfulfilled',
        ]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 3,
            'unit_price' => 1000,
            'line_total' => 3000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/orders/show')
            ->where('canCreateShipment', true)
            ->where('order.fulfillment_summary.total_remaining_quantity', 3)
            ->where('order.fulfillment_summary.shipping_summary', 'no_shipment')
            ->where('order.items.0.id', $item->id)
            ->where('order.items.0.remaining_quantity', 3)
        );
    }
}
