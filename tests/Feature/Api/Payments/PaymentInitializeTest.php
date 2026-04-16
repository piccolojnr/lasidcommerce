<?php

namespace Tests\Feature\Api\Payments;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentInitializeTest extends TestCase
{
    use RefreshDatabase;

    private function paystackOk(string $reference = 'PAY-TEST123'): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status'  => true,
                'message' => 'Authorization URL created',
                'data'    => [
                    'authorization_url' => 'https://checkout.paystack.com/abc123',
                    'access_code'       => 'abc123',
                    'reference'         => $reference,
                ],
            ], 200),
        ]);
    }

    private function pendingOrder(User $user, array $attrs = []): Order
    {
        return Order::factory()->create(array_merge([
            'user_id'         => $user->id,
            'email'           => $user->email,
            'status'          => 'pending',
            'payment_status'  => 'unpaid',
            'total_amount'    => 5000,
            'currency_code'   => 'GHS',
            'order_number'    => 'ORD-' . now()->format('Ymd') . '-TEST01',
        ], $attrs));
    }

    // --- tests ---

    public function test_initializes_payment_successfully(): void
    {
        $this->paystackOk();

        $user  = User::factory()->create();
        $order = $this->pendingOrder($user);

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['authorization_url', 'access_code', 'reference'],
            ]);

        $this->assertSame('https://checkout.paystack.com/abc123', $response->json('data.authorization_url'));
    }

    public function test_authorization_url_returned(): void
    {
        $this->paystackOk();

        $user  = User::factory()->create();
        $order = $this->pendingOrder($user);

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);

        $response->assertOk();
        $this->assertNotNull($response->json('data.authorization_url'));
        $this->assertNotNull($response->json('data.access_code'));
        $this->assertNotNull($response->json('data.reference'));
    }

    public function test_payment_row_created(): void
    {
        $this->paystackOk();

        $user  = User::factory()->create();
        $order = $this->pendingOrder($user);

        $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id'  => $order->id,
            'user_id'   => $user->id,
            'provider'  => 'paystack',
            'status'    => 'pending',
            'amount'    => 5000,
        ]);
    }

    public function test_correct_amount_sent_to_provider(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => function ($request) {
                $body = $request->data();
                \PHPUnit\Framework\Assert::assertSame(8000, $body['amount']);

                return Http::response([
                    'status' => true,
                    'data'   => [
                        'authorization_url' => 'https://checkout.paystack.com/xyz',
                        'access_code'       => 'xyz',
                        'reference'         => 'PAY-XYZ',
                    ],
                ], 200);
            },
        ]);

        $user  = User::factory()->create();
        $order = $this->pendingOrder($user, ['total_amount' => 8000]);

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);

        $response->assertOk();
    }

    public function test_cannot_initialize_another_users_order(): void
    {
        $this->paystackOk();

        $user  = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->pendingOrder($other);

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);

        $response->assertNotFound();
    }

    public function test_cannot_initialize_paid_order(): void
    {
        $user  = User::factory()->create();
        $order = $this->pendingOrder($user, ['payment_status' => 'paid']);

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_cannot_initialize_cancelled_order(): void
    {
        $user  = User::factory()->create();
        $order = $this->pendingOrder($user, ['status' => 'cancelled']);

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/payments/initialize', ['order_id' => 1]);

        $response->assertUnauthorized();
    }

    public function test_returns_not_found_for_missing_order(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => 999999,
        ]);

        $response->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Order not found.');
    }

    public function test_reuses_existing_pending_payment_attempt(): void
    {
        $this->paystackOk('PAY-REUSED-001');

        $user  = User::factory()->create();
        $order = $this->pendingOrder($user);

        $firstResponse = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);
        $secondResponse = $this->actingAsCustomer($user)->postJson('/api/v1/payments/initialize', [
            'order_id' => $order->id,
        ]);

        $firstResponse->assertOk();
        $secondResponse->assertOk();
        $this->assertSame(
            $firstResponse->json('data.reference'),
            $secondResponse->json('data.reference'),
        );
        $this->assertSame(1, \App\Models\Payment::where('order_id', $order->id)->count());
    }
}

