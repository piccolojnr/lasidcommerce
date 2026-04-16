<?php

namespace Tests\Feature\Webhooks;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\InternalPaymentReceivedNotification;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test_paystack_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.paystack.secret_key' => self::SECRET]);
    }

    // --- helpers ---

    private function sign(string $payload): string
    {
        return hash_hmac('sha512', $payload, self::SECRET);
    }

    private function chargeSuccessPayload(string $reference, int $amount = 5000, array $overrides = []): string
    {
        return json_encode(array_merge([
            'event' => 'charge.success',
            'data'  => array_merge([
                'id'               => 987654321,
                'reference'        => $reference,
                'amount'           => $amount,
                'currency'         => 'GHS',
                'status'           => 'success',
                'gateway_response' => 'Successful',
            ], $overrides),
        ], []));
    }

    private function postRaw(string $payload, string $signature): \Illuminate\Testing\TestResponse
    {
        return $this->call(
            'POST',
            '/webhooks/paystack',
            [],
            [],
            [],
            [
                'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
                'CONTENT_TYPE'             => 'application/json',
            ],
            $payload,
        );
    }

    private function pendingPayment(array $orderAttrs = []): Payment
    {
        $user  = User::factory()->create();
        $order = Order::factory()->create(array_merge([
            'user_id'        => $user->id,
            'email'          => $user->email,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'total_amount'   => 5000,
        ], $orderAttrs));

        return Payment::factory()->create([
            'order_id'  => $order->id,
            'user_id'   => $user->id,
            'provider'  => 'paystack',
            'reference' => 'PAY-TESTREF001',
            'status'    => 'pending',
            'amount'    => 5000,
        ]);
    }

    // --- tests ---

    public function test_rejects_invalid_signature(): void
    {
        $payload   = $this->chargeSuccessPayload('PAY-TESTREF001');
        $badSig    = 'invalidsignature';

        $response = $this->postRaw($payload, $badSig);

        $response->assertUnauthorized();
    }

    public function test_logs_webhook_payload(): void
    {
        $this->pendingPayment();
        $payload = $this->chargeSuccessPayload('PAY-TESTREF001');
        $sig     = $this->sign($payload);

        $this->postRaw($payload, $sig);

        $this->assertDatabaseHas('payment_webhook_logs', [
            'provider'   => 'paystack',
            'event_type' => 'charge.success',
            'reference'  => 'PAY-TESTREF001',
        ]);
    }

    public function test_marks_payment_successful(): void
    {
        Notification::fake();
        config()->set('notifications.internal.recipients', ['ops@example.com']);
        $payment = $this->pendingPayment();
        $payload = $this->chargeSuccessPayload('PAY-TESTREF001');
        $sig     = $this->sign($payload);

        $this->postRaw($payload, $sig)->assertOk();

        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull($payment->fresh()->paid_at);
        Notification::assertSentOnDemand(PaymentReceivedNotification::class);
        Notification::assertSentOnDemand(InternalPaymentReceivedNotification::class);
    }

    public function test_updates_order_payment_status(): void
    {
        $payment = $this->pendingPayment();
        $order   = $payment->order;
        $payload = $this->chargeSuccessPayload('PAY-TESTREF001');
        $sig     = $this->sign($payload);

        $this->postRaw($payload, $sig)->assertOk();

        $this->assertDatabaseHas('orders', [
            'id'             => $order->id,
            'payment_status' => 'paid',
            'status'         => 'confirmed',
        ]);
    }

    public function test_creates_order_status_history(): void
    {
        $payment = $this->pendingPayment();
        $order   = $payment->order;
        $payload = $this->chargeSuccessPayload('PAY-TESTREF001');
        $sig     = $this->sign($payload);

        $this->postRaw($payload, $sig)->assertOk();

        $this->assertDatabaseHas('order_status_histories', [
            'order_id'   => $order->id,
            'to_status'  => 'confirmed',
        ]);
    }

    public function test_idempotent_on_repeated_webhook(): void
    {
        $payment = $this->pendingPayment();
        $payload = $this->chargeSuccessPayload('PAY-TESTREF001');
        $sig     = $this->sign($payload);

        // First call
        $this->postRaw($payload, $sig)->assertOk();

        // Second identical call
        $this->postRaw($payload, $sig)->assertOk();

        // Only one status history entry for 'confirmed' despite two calls
        $this->assertSame(1, \App\Models\OrderStatusHistory::where('to_status', 'confirmed')->count());
    }

    public function test_ignores_unsupported_events_safely(): void
    {
        $payload = json_encode([
            'event' => 'transfer.success',
            'data'  => ['reference' => 'SOME-REF'],
        ]);
        $sig = $this->sign($payload);

        $response = $this->postRaw($payload, $sig);

        $response->assertOk();
        $this->assertDatabaseHas('payment_webhook_logs', [
            'event_type' => 'transfer.success',
            'processed'  => true,
        ]);
    }

    public function test_handles_unknown_reference_safely(): void
    {
        $payload = $this->chargeSuccessPayload('PAY-DOESNOTEXIST');
        $sig     = $this->sign($payload);

        $response = $this->postRaw($payload, $sig);

        $response->assertOk();
        $this->assertDatabaseHas('payment_webhook_logs', [
            'reference' => 'PAY-DOESNOTEXIST',
            'processed' => true,
        ]);
    }

    public function test_does_not_reopen_cancelled_order_on_late_success_webhook(): void
    {
        $payment = $this->pendingPayment([
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
        ]);
        $payload = $this->chargeSuccessPayload('PAY-TESTREF001');
        $sig     = $this->sign($payload);

        $this->postRaw($payload, $sig)->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $payment->order_id,
            'status' => 'cancelled',
            'payment_status' => 'paid',
        ]);
    }
}
