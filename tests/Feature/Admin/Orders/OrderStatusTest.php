<?php

namespace Tests\Feature\Admin\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\InternalOrderStatusUpdatedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage orders', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage orders');
    }

    private function order(string $status = 'pending'): Order
    {
        return Order::factory()->create(['status' => $status]);
    }

    private function updateStatus(Order $order, array $data): TestResponse
    {
        return $this->actingAs($this->admin)
            ->patch(route('admin.orders.status.update', $order), $data);
    }

    // --- authorization ---

    public function test_guest_cannot_update_order_status(): void
    {
        $order = $this->order();

        $response = $this->patch(route('admin.orders.status.update', $order), ['status' => 'confirmed']);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_cannot_update_order_status(): void
    {
        $user = User::factory()->create(); // no permission
        $order = $this->order();

        $response = $this->actingAs($user)
            ->patch(route('admin.orders.status.update', $order), ['status' => 'confirmed']);

        $response->assertForbidden();
    }

    // --- valid transitions ---

    public function test_valid_transition_succeeds(): void
    {
        Notification::fake();
        config()->set('notifications.internal.recipients', ['ops@example.com']);
        $order = $this->order('pending');

        $response = $this->updateStatus($order, ['status' => 'confirmed']);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);
        Notification::assertSentOnDemand(OrderStatusUpdatedNotification::class);
        Notification::assertSentOnDemand(InternalOrderStatusUpdatedNotification::class);
    }

    public function test_pending_to_confirmed(): void
    {
        $order = $this->order('pending');
        $this->updateStatus($order, ['status' => 'confirmed']);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_confirmed_to_processing(): void
    {
        $order = $this->order('confirmed');
        $this->updateStatus($order, ['status' => 'processing']);
        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_processing_to_completed_when_fulfilled(): void
    {
        $order = Order::factory()->create([
            'status' => 'processing',
            'fulfillment_status' => 'fulfilled',
        ]);
        $this->updateStatus($order, ['status' => 'completed']);
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_pending_can_be_cancelled(): void
    {
        $order = $this->order('pending');
        $this->updateStatus($order, ['status' => 'cancelled']);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_confirmed_can_be_cancelled(): void
    {
        $order = $this->order('confirmed');
        $this->updateStatus($order, ['status' => 'cancelled']);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_processing_can_be_cancelled(): void
    {
        $order = $this->order('processing');
        $this->updateStatus($order, ['status' => 'cancelled']);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_cancelling_order_releases_reserved_stock(): void
    {
        $product = Product::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_reserved' => 2,
        ]);
        $order = $this->order('confirmed');
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 1000,
            'line_total' => 2000,
        ]);

        $this->updateStatus($order, ['status' => 'cancelled']);

        $stockItem->refresh();
        $this->assertSame(5, $stockItem->quantity_on_hand);
        $this->assertSame(0, $stockItem->quantity_reserved);
    }

    public function test_completing_order_commits_reserved_stock(): void
    {
        $product = Product::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_reserved' => 2,
        ]);
        $order = Order::factory()->create([
            'status' => 'processing',
            'fulfillment_status' => 'fulfilled',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 1000,
            'line_total' => 2000,
        ]);

        $this->updateStatus($order, ['status' => 'completed']);

        $stockItem->refresh();
        $this->assertSame(3, $stockItem->quantity_on_hand);
        $this->assertSame(0, $stockItem->quantity_reserved);

        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'type' => StockMovement::TYPE_SALE,
            'quantity' => 2,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
            'note' => 'Order completed.',
        ]);
    }

    // --- invalid transitions ---

    public function test_invalid_transition_fails_with_error(): void
    {
        $order = $this->order('pending');

        $response = $this->updateStatus($order, ['status' => 'completed']); // skip steps

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHasErrors('status');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancelled_order_cannot_be_transitioned(): void
    {
        $order = $this->order('cancelled');

        $response = $this->updateStatus($order, ['status' => 'pending']);

        $response->assertSessionHasErrors('status');
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_completed_order_cannot_be_transitioned(): void
    {
        $order = $this->order('completed');

        $response = $this->updateStatus($order, ['status' => 'processing']);

        $response->assertSessionHasErrors('status');
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_processing_cannot_complete_when_not_fulfilled(): void
    {
        $order = $this->order('processing');

        $response = $this->updateStatus($order, ['status' => 'completed']);

        $response->assertSessionHasErrors('status');
        $this->assertSame('processing', $order->fresh()->status);
    }

    // --- history ---

    public function test_history_row_created_on_transition(): void
    {
        $order = $this->order('pending');

        $this->updateStatus($order, ['status' => 'confirmed', 'note' => 'Manual confirmation.']);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'confirmed',
            'note' => 'Manual confirmation.',
            'changed_by' => $this->admin->id,
        ]);
    }

    public function test_no_history_row_created_on_invalid_transition(): void
    {
        $order = $this->order('cancelled');

        $this->updateStatus($order, ['status' => 'confirmed']);

        $this->assertDatabaseCount('order_status_histories', 0);
    }
}
