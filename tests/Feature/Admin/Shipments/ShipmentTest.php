<?php

namespace Tests\Feature\Admin\Shipments;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage shipments', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage shipments');
    }

    // --- helpers ---

    private function processingOrder(): Order
    {
        return Order::factory()->create(['status' => 'processing']);
    }

    private function orderItemFor(Order $order): OrderItem
    {
        return OrderItem::factory()->create(['order_id' => $order->id]);
    }

    private function shipmentItemFor(Shipment $shipment, OrderItem $orderItem, int $quantity = 1): void
    {
        \DB::table('shipment_items')->insert([
            'shipment_id' => $shipment->id,
            'order_item_id' => $orderItem->id,
            'quantity' => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function storeShipment(array $data): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin)
            ->post(route('admin.shipments.store'), $data);
    }

    private function updateStatus(Shipment $shipment, string $status): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin)
            ->patch(route('admin.shipments.status.update', $shipment), ['status' => $status]);
    }

    private function quickStoreShipment(Order $order): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin)
            ->post(route('admin.orders.shipments.quick-store', $order));
    }

    // --- authorization: store ---

    public function test_guest_cannot_create_shipment(): void
    {
        $order = $this->processingOrder();
        $item  = $this->orderItemFor($order);

        $response = $this->post(route('admin.shipments.store'), [
            'order_id' => $order->id,
            'items'    => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_cannot_create_shipment(): void
    {
        $user  = User::factory()->create();
        $order = $this->processingOrder();
        $item  = $this->orderItemFor($order);

        $response = $this->actingAs($user)
            ->post(route('admin.shipments.store'), [
                'order_id' => $order->id,
                'items'    => [['order_item_id' => $item->id, 'quantity' => 1]],
            ]);

        $response->assertForbidden();
    }

    // --- authorization: status update ---

    public function test_guest_cannot_update_shipment_status(): void
    {
        $shipment = Shipment::factory()->create();

        $response = $this->patch(route('admin.shipments.status.update', $shipment), ['status' => 'packed']);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_cannot_update_shipment_status(): void
    {
        $user     = User::factory()->create();
        $shipment = Shipment::factory()->create();

        $response = $this->actingAs($user)
            ->patch(route('admin.shipments.status.update', $shipment), ['status' => 'packed']);

        $response->assertForbidden();
    }

    // --- create shipment ---

    public function test_can_create_shipment_for_processing_order(): void
    {
        $order = $this->processingOrder();
        $item  = $this->orderItemFor($order);

        $response = $this->storeShipment([
            'order_id' => $order->id,
            'items'    => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shipments', ['order_id' => $order->id, 'status' => 'pending']);
    }

    public function test_shipment_items_are_created(): void
    {
        $order = $this->processingOrder();
        $item  = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'unit_price' => 1000,
            'line_total' => 2000,
        ]);

        $this->storeShipment([
            'order_id' => $order->id,
            'items'    => [['order_item_id' => $item->id, 'quantity' => 2]],
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'order_item_id' => $item->id,
            'quantity'      => 2,
        ]);
    }

    public function test_creating_shipment_marks_order_partially_fulfilled(): void
    {
        $order = $this->processingOrder();
        $item = $this->orderItemFor($order);

        $this->storeShipment([
            'order_id' => $order->id,
            'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);

        $this->assertSame('partially_fulfilled', $order->fresh()->fulfillment_status);
    }

    public function test_cannot_create_shipment_beyond_remaining_quantity(): void
    {
        $order = $this->processingOrder();
        $item = $this->orderItemFor($order);

        $this->storeShipment([
            'order_id' => $order->id,
            'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);

        $response = $this->storeShipment([
            'order_id' => $order->id,
            'items' => [['order_item_id' => $item->id, 'quantity' => $item->quantity]],
        ]);

        $response->assertSessionHasErrors('order_id');
    }

    public function test_split_shipments_can_cover_remaining_quantity_without_exceeding_it(): void
    {
        $order = $this->processingOrder();
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'line_total' => 2000,
            'unit_price' => 1000,
        ]);

        $first = $this->storeShipment([
            'order_id' => $order->id,
            'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);
        $second = $this->storeShipment([
            'order_id' => $order->id,
            'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);

        $first->assertRedirect();
        $second->assertRedirect();
        $this->assertDatabaseCount('shipments', 2);
    }

    public function test_quick_create_shipment_uses_all_remaining_quantities(): void
    {
        $order = $this->processingOrder();
        $firstItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'line_total' => 2000,
            'unit_price' => 1000,
        ]);
        $secondItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 3,
            'line_total' => 3000,
            'unit_price' => 1000,
        ]);

        $response = $this->quickStoreShipment($order);

        $response->assertRedirect();
        $shipment = Shipment::query()->where('order_id', $order->id)->latest('id')->firstOrFail();

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'order_item_id' => $firstItem->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'order_item_id' => $secondItem->id,
            'quantity' => 3,
        ]);
    }

    public function test_quick_create_shipment_only_uses_remaining_quantities(): void
    {
        $order = $this->processingOrder();
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 3,
            'line_total' => 3000,
            'unit_price' => 1000,
        ]);

        $this->storeShipment([
            'order_id' => $order->id,
            'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);

        $response = $this->quickStoreShipment($order);

        $response->assertRedirect();
        $shipment = Shipment::query()->where('order_id', $order->id)->latest('id')->firstOrFail();

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'order_item_id' => $item->id,
            'quantity' => 2,
        ]);
    }

    public function test_cannot_create_shipment_for_non_processing_order(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $item  = $this->orderItemFor($order);

        $response = $this->storeShipment([
            'order_id' => $order->id,
            'items'    => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertSessionHasErrors('order_id');
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_cannot_create_shipment_for_confirmed_order(): void
    {
        $order = Order::factory()->create(['status' => 'confirmed']);
        $item  = $this->orderItemFor($order);

        $response = $this->storeShipment([
            'order_id' => $order->id,
            'items'    => [['order_item_id' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertSessionHasErrors('order_id');
    }

    public function test_quick_create_shipment_fails_for_non_processing_order(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $this->orderItemFor($order);

        $response = $this->quickStoreShipment($order);

        $response->assertSessionHasErrors('order_id');
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_quick_create_shipment_fails_when_nothing_remains_to_ship(): void
    {
        $order = $this->processingOrder();
        $item = $this->orderItemFor($order);

        $this->storeShipment([
            'order_id' => $order->id,
            'items' => [['order_item_id' => $item->id, 'quantity' => $item->quantity]],
        ]);

        $response = $this->quickStoreShipment($order);

        $response->assertSessionHasErrors('order_id');
        $this->assertDatabaseCount('shipments', 1);
    }

    public function test_store_requires_at_least_one_item(): void
    {
        $order = $this->processingOrder();

        $response = $this->storeShipment([
            'order_id' => $order->id,
            'items'    => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_optional_carrier_fields_are_stored(): void
    {
        $order = $this->processingOrder();
        $item  = $this->orderItemFor($order);

        $this->storeShipment([
            'order_id'        => $order->id,
            'items'           => [['order_item_id' => $item->id, 'quantity' => 1]],
            'carrier_name'    => 'DHL',
            'tracking_number' => 'DHL12345',
        ]);

        $this->assertDatabaseHas('shipments', [
            'order_id'        => $order->id,
            'carrier_name'    => 'DHL',
            'tracking_number' => 'DHL12345',
        ]);
    }

    // --- valid status transitions ---

    public function test_pending_to_packed(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $this->updateStatus($shipment, 'packed');

        $this->assertSame('packed', $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->packed_at);
    }

    public function test_packed_to_shipped(): void
    {
        $shipment = Shipment::factory()->packed()->create();

        $this->updateStatus($shipment, 'shipped');

        $this->assertSame('shipped', $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->shipped_at);
    }

    public function test_shipped_to_in_transit(): void
    {
        $shipment = Shipment::factory()->shipped()->create();

        $this->updateStatus($shipment, 'in_transit');

        $this->assertSame('in_transit', $shipment->fresh()->status);
    }

    public function test_shipped_to_delivered(): void
    {
        $shipment = Shipment::factory()->shipped()->create();

        $this->updateStatus($shipment, 'delivered');

        $this->assertSame('delivered', $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->delivered_at);
    }

    public function test_delivered_to_returned(): void
    {
        $shipment = Shipment::factory()->delivered()->create();

        $this->updateStatus($shipment, 'returned');

        $this->assertSame('returned', $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->returned_at);
    }

    public function test_pending_can_be_cancelled(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $this->updateStatus($shipment, 'cancelled');

        $this->assertSame('cancelled', $shipment->fresh()->status);
    }

    public function test_packed_can_be_cancelled(): void
    {
        $shipment = Shipment::factory()->packed()->create();

        $this->updateStatus($shipment, 'cancelled');

        $this->assertSame('cancelled', $shipment->fresh()->status);
    }

    public function test_shipped_can_fail(): void
    {
        $shipment = Shipment::factory()->shipped()->create();

        $this->updateStatus($shipment, 'failed');

        $this->assertSame('failed', $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->failed_at);
    }

    // --- invalid transitions ---

    public function test_invalid_transition_fails_with_error(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $response = $this->updateStatus($shipment, 'delivered'); // skip steps

        $response->assertSessionHasErrors('status');
        $this->assertSame('pending', $shipment->fresh()->status);
    }

    public function test_cancelled_shipment_cannot_be_transitioned(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'cancelled']);

        $response = $this->updateStatus($shipment, 'packed');

        $response->assertSessionHasErrors('status');
        $this->assertSame('cancelled', $shipment->fresh()->status);
    }

    public function test_failed_shipment_cannot_be_transitioned(): void
    {
        $shipment = Shipment::factory()->create([
            'status'    => 'failed',
            'failed_at' => now(),
        ]);

        $response = $this->updateStatus($shipment, 'shipped');

        $response->assertSessionHasErrors('status');
        $this->assertSame('failed', $shipment->fresh()->status);
    }

    public function test_returned_shipment_cannot_be_transitioned(): void
    {
        $shipment = Shipment::factory()->delivered()->create();
        $shipment->update(['status' => 'returned', 'returned_at' => now()]);

        $response = $this->updateStatus($shipment, 'delivered');

        $response->assertSessionHasErrors('status');
        $this->assertSame('returned', $shipment->fresh()->status);
    }

    // --- fulfillment sync ---

    public function test_delivered_shipment_marks_order_fulfilled(): void
    {
        $order    = Order::factory()->create([
            'status'             => 'processing',
            'fulfillment_status' => 'unfulfilled',
        ]);
        $shipment = Shipment::factory()->shipped()->create(['order_id' => $order->id]);
        $item = $this->orderItemFor($order);
        $this->shipmentItemFor($shipment, $item, $item->quantity);

        $this->updateStatus($shipment, 'delivered');

        $this->assertSame('fulfilled', $order->fresh()->fulfillment_status);
        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_non_delivery_transition_does_not_change_fulfillment(): void
    {
        $order    = Order::factory()->create([
            'status'             => 'processing',
            'fulfillment_status' => 'unfulfilled',
        ]);
        $shipment = Shipment::factory()->create(['order_id' => $order->id, 'status' => 'pending']);
        $item = $this->orderItemFor($order);
        $this->shipmentItemFor($shipment, $item, 1);

        $this->updateStatus($shipment, 'packed');

        $this->assertSame('partially_fulfilled', $order->fresh()->fulfillment_status);
    }

    public function test_shipped_shipment_does_not_change_order_business_status(): void
    {
        $order = Order::factory()->create([
            'status' => 'processing',
            'fulfillment_status' => 'partially_fulfilled',
        ]);
        $shipment = Shipment::factory()->packed()->create(['order_id' => $order->id]);
        $item = $this->orderItemFor($order);
        $this->shipmentItemFor($shipment, $item, 1);

        $this->updateStatus($shipment, 'shipped');

        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_returned_shipment_reopens_remaining_quantity(): void
    {
        $order = Order::factory()->create([
            'status' => 'processing',
            'fulfillment_status' => 'fulfilled',
        ]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'line_total' => 2000,
            'unit_price' => 1000,
        ]);
        $shipment = Shipment::factory()->delivered()->create(['order_id' => $order->id]);
        $this->shipmentItemFor($shipment, $item, 2);

        $this->updateStatus($shipment, 'returned');

        $order = $order->fresh([
            'orderItems.shipmentItems.shipment',
            'shipments',
        ]);

        $this->assertSame('unfulfilled', $order->fulfillment_status);
        $summary = app(\App\Domain\Order\Services\OrderFulfillmentService::class)->summarize($order);
        $this->assertSame(2, $summary['total_remaining_quantity']);
        $this->assertSame('attention_required', $summary['shipping_summary']);
    }

    public function test_failed_shipment_reopens_remaining_quantity(): void
    {
        $order = Order::factory()->create([
            'status' => 'processing',
            'fulfillment_status' => 'partially_fulfilled',
        ]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'line_total' => 2000,
            'unit_price' => 1000,
        ]);
        $shipment = Shipment::factory()->shipped()->create(['order_id' => $order->id]);
        $this->shipmentItemFor($shipment, $item, 2);

        $this->updateStatus($shipment, 'failed');

        $order = $order->fresh([
            'orderItems.shipmentItems.shipment',
            'shipments',
        ]);

        $this->assertSame('unfulfilled', $order->fulfillment_status);
        $summary = app(\App\Domain\Order\Services\OrderFulfillmentService::class)->summarize($order);
        $this->assertSame(2, $summary['total_remaining_quantity']);
        $this->assertSame('attention_required', $summary['shipping_summary']);
    }

    // --- redirect behaviour ---

    public function test_valid_status_update_redirects_to_show(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $response = $this->updateStatus($shipment, 'packed');

        $response->assertRedirect(route('admin.shipments.show', $shipment));
    }

    public function test_invalid_status_update_redirects_with_errors(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $response = $this->updateStatus($shipment, 'delivered');

        $response->assertRedirect(route('admin.shipments.show', $shipment));
        $response->assertSessionHasErrors('status');
    }
}
