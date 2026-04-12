<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTimelineTest extends TestCase
{
    use RefreshDatabase;

    // --- helpers ---

    private function user(): User
    {
        return User::factory()->create();
    }

    private function orderFor(User $user, array $attrs = []): Order
    {
        return Order::factory()->create(array_merge(['user_id' => $user->id], $attrs));
    }

    private function getTimeline(User $user, Order $order): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->getJson(route('api.v1.orders.timeline', $order));
    }

    // --- authorization ---

    public function test_guest_cannot_view_timeline(): void
    {
        $order = Order::factory()->create();

        $this->getJson(route('api.v1.orders.timeline', $order))->assertUnauthorized();
    }

    public function test_user_cannot_view_another_users_timeline(): void
    {
        $userA = $this->user();
        $order = $this->orderFor($this->user()); // belongs to userB

        $this->getTimeline($userA, $order)->assertNotFound();
    }

    // --- event presence ---

    public function test_timeline_includes_order_placed_event(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user, ['placed_at' => now()]);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertContains('order_placed', $types);
    }

    public function test_timeline_includes_payment_confirmed_event(): void
    {
        $user    = $this->user();
        $order   = $this->orderFor($user);
        Payment::factory()->create([
            'order_id' => $order->id,
            'user_id'  => $user->id,
            'status'   => 'paid',
            'paid_at'  => now(),
        ]);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertContains('payment_confirmed', $types);
    }

    public function test_timeline_includes_order_status_history_events(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user);
        OrderStatusHistory::create([
            'order_id'    => $order->id,
            'from_status' => 'pending',
            'to_status'   => 'confirmed',
            'note'        => null,
            'changed_by'  => null,
        ]);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertContains('order_confirmed', $types);
    }

    public function test_timeline_includes_shipment_packed_event(): void
    {
        $user     = $this->user();
        $order    = $this->orderFor($user);
        Shipment::factory()->packed()->create(['order_id' => $order->id]);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertContains('shipment_packed', $types);
    }

    public function test_timeline_includes_shipment_shipped_event(): void
    {
        $user     = $this->user();
        $order    = $this->orderFor($user);
        Shipment::factory()->shipped()->create(['order_id' => $order->id]);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertContains('shipment_shipped', $types);
    }

    public function test_timeline_includes_shipment_delivered_event(): void
    {
        $user     = $this->user();
        $order    = $this->orderFor($user);
        Shipment::factory()->delivered()->create(['order_id' => $order->id]);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertContains('shipment_delivered', $types);
    }

    public function test_timeline_omits_shipment_events_when_no_shipment(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        foreach (['shipment_packed', 'shipment_shipped', 'shipment_delivered'] as $type) {
            $this->assertNotContains($type, $types);
        }
    }

    // --- shape ---

    public function test_timeline_has_correct_event_shape(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user, ['placed_at' => now()]);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $event = $response->json('data.0');
        $this->assertArrayHasKey('type', $event);
        $this->assertArrayHasKey('label', $event);
        $this->assertArrayHasKey('description', $event);
        $this->assertArrayHasKey('occurred_at', $event);
    }

    // --- ordering ---

    public function test_timeline_is_sorted_chronologically(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user, ['placed_at' => now()->subHours(3)]);

        // Payment happened 2h ago
        Payment::factory()->create([
            'order_id' => $order->id,
            'user_id'  => $user->id,
            'status'   => 'paid',
            'paid_at'  => now()->subHours(2),
        ]);

        // Status history confirmed 1h ago
        OrderStatusHistory::create([
            'order_id'    => $order->id,
            'from_status' => 'pending',
            'to_status'   => 'confirmed',
            'note'        => null,
            'changed_by'  => null,
            'created_at'  => now()->subHour(),
        ]);

        $response = $this->getTimeline($user, $order);

        $response->assertOk();
        $events      = $response->json('data');
        $occurredAts = array_column($events, 'occurred_at');
        $sorted      = $occurredAts;
        sort($sorted);

        $this->assertSame($sorted, $occurredAts, 'Timeline events are not in chronological order.');
    }
}
