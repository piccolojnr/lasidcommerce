<?php

namespace Tests\Feature\Admin\Orders;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    private function updateStatus(Order $order, array $data): \Illuminate\Testing\TestResponse
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
        $user  = User::factory()->create(); // no permission
        $order = $this->order();

        $response = $this->actingAs($user)
            ->patch(route('admin.orders.status.update', $order), ['status' => 'confirmed']);

        $response->assertForbidden();
    }

    // --- valid transitions ---

    public function test_valid_transition_succeeds(): void
    {
        $order = $this->order('pending');

        $response = $this->updateStatus($order,['status' => 'confirmed']);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);
    }

    public function test_pending_to_confirmed(): void
    {
        $order = $this->order('pending');
        $this->updateStatus($order,['status' => 'confirmed']);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_confirmed_to_processing(): void
    {
        $order = $this->order('confirmed');
        $this->updateStatus($order,['status' => 'processing']);
        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_processing_to_shipped(): void
    {
        $order = $this->order('processing');
        $this->updateStatus($order,['status' => 'shipped']);
        $this->assertSame('shipped', $order->fresh()->status);
    }

    public function test_shipped_to_delivered(): void
    {
        $order = $this->order('shipped');
        $this->updateStatus($order,['status' => 'delivered']);
        $this->assertSame('delivered', $order->fresh()->status);
    }

    public function test_delivered_to_completed(): void
    {
        $order = $this->order('delivered');
        $this->updateStatus($order,['status' => 'completed']);
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_pending_can_be_cancelled(): void
    {
        $order = $this->order('pending');
        $this->updateStatus($order,['status' => 'cancelled']);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_confirmed_can_be_cancelled(): void
    {
        $order = $this->order('confirmed');
        $this->updateStatus($order,['status' => 'cancelled']);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_processing_can_be_cancelled(): void
    {
        $order = $this->order('processing');
        $this->updateStatus($order,['status' => 'cancelled']);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    // --- invalid transitions ---

    public function test_invalid_transition_fails_with_error(): void
    {
        $order = $this->order('pending');

        $response = $this->updateStatus($order,['status' => 'shipped']); // skip steps

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHasErrors('status');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancelled_order_cannot_be_transitioned(): void
    {
        $order = $this->order('cancelled');

        $response = $this->updateStatus($order,['status' => 'pending']);

        $response->assertSessionHasErrors('status');
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_completed_order_cannot_be_transitioned(): void
    {
        $order = $this->order('completed');

        $response = $this->updateStatus($order,['status' => 'delivered']);

        $response->assertSessionHasErrors('status');
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_shipped_cannot_go_back_to_processing(): void
    {
        $order = $this->order('shipped');

        $response = $this->updateStatus($order,['status' => 'processing']);

        $response->assertSessionHasErrors('status');
        $this->assertSame('shipped', $order->fresh()->status);
    }

    // --- history ---

    public function test_history_row_created_on_transition(): void
    {
        $order = $this->order('pending');

        $this->updateStatus($order,['status' => 'confirmed', 'note' => 'Manual confirmation.']);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id'    => $order->id,
            'from_status' => 'pending',
            'to_status'   => 'confirmed',
            'note'        => 'Manual confirmation.',
            'changed_by'  => $this->admin->id,
        ]);
    }

    public function test_no_history_row_created_on_invalid_transition(): void
    {
        $order = $this->order('cancelled');

        $this->updateStatus($order,['status' => 'confirmed']);

        $this->assertDatabaseCount('order_status_histories', 0);
    }
}
