<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Notifications\CustomerMagicLinkNotification;
use App\Notifications\CustomerResetPasswordNotification;
use App\Notifications\CustomerWelcomeNotification;
use App\Notifications\InternalNewCustomerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_endpoint_reports_guest_state(): void
    {
        $response = $this->getJson('/api/v1/auth/session');

        $response->assertOk()
            ->assertJsonPath('data.authenticated', false)
            ->assertJsonPath('data.user', null);
    }

    public function test_magic_link_request_sends_notification_for_customer_account(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'customer@example.com']);

        $this->postJson('/api/v1/auth/magic-link/request', [
            'email' => $user->email,
            'redirect_to' => '/orders',
        ])->assertOk();

        Notification::assertSentOnDemand(CustomerMagicLinkNotification::class, function (CustomerMagicLinkNotification $notification) {
            $this->assertStringContainsString('/auth/verify?token=', $notification->verifyUrl);

            return true;
        });
    }

    public function test_magic_link_request_does_not_send_notification_for_platform_users(): void
    {
        Notification::fake();

        $staff = User::factory()->create(['email' => 'staff@example.com']);
        $staff->assignRole(Role::findOrCreate('support_agent', 'web'));

        $this->postJson('/api/v1/auth/magic-link/request', [
            'email' => $staff->email,
        ])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_magic_link_request_respects_customer_notification_opt_out(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'notification_preferences' => [
                'auth_magic_link' => false,
            ],
        ]);

        $this->postJson('/api/v1/auth/magic-link/request', [
            'email' => $user->email,
        ])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_magic_link_verification_creates_customer_and_adopts_guest_cart(): void
    {
        Notification::fake();
        config()->set('notifications.internal.recipients', ['ops@example.com']);
        config()->set('storefront.url', 'http://shop.example.test');

        $guestCart = Cart::factory()->create([
            'user_id' => null,
            'session_id' => 'guest-cart-token',
            'status' => 'active',
        ]);

        CartItem::factory()->create([
            'cart_id' => $guestCart->id,
            'product_variant_id' => null,
            'quantity' => 2,
            'unit_price' => 1500,
            'line_total' => 3000,
        ]);

        $this->postJson('/api/v1/auth/magic-link/request', [
            'email' => 'new-customer@example.com',
            'cart_token' => 'guest-cart-token',
            'redirect_to' => '/account/orders',
        ])->assertOk();

        $plainToken = null;

        Notification::assertSentOnDemand(CustomerMagicLinkNotification::class, function (CustomerMagicLinkNotification $notification) use (&$plainToken) {
            $url = parse_url($notification->verifyUrl);
            parse_str($url['query'] ?? '', $params);
            $plainToken = $params['token'] ?? null;

            return true;
        });

        $this->assertNotNull($plainToken);

        $response = $this->postJson('/api/v1/auth/magic-link/verify', ['token' => $plainToken]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'new-customer@example.com')
            ->assertJsonStructure(['data' => ['token', 'user', 'redirect_to', 'was_created']]);

        $this->assertNotEmpty($response->json('data.token'));

        $user = User::where('email', 'new-customer@example.com')->firstOrFail();
        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('', $user->name);
        Notification::assertSentTo($user, CustomerWelcomeNotification::class);
        Notification::assertSentOnDemand(InternalNewCustomerNotification::class, function (InternalNewCustomerNotification $notification, array $channels, object $notifiable) {
            return $notifiable->routes['mail'] === 'ops@example.com'
                && $notification->user->email === 'new-customer@example.com';
        });

        $adoptedCart = Cart::active()->where('user_id', $user->id)->first();
        $this->assertNotNull($adoptedCart);
        $this->assertSame(3000, $adoptedCart->fresh()->total_amount);
    }

    public function test_magic_link_cannot_be_used_twice(): void
    {
        Notification::fake();

        config()->set('storefront.url', 'http://shop.example.test');

        $this->postJson('/api/v1/auth/magic-link/request', [
            'email' => 'repeat@example.com',
        ])->assertOk();

        $plainToken = null;

        Notification::assertSentOnDemand(CustomerMagicLinkNotification::class, function (CustomerMagicLinkNotification $notification) use (&$plainToken) {
            $url = parse_url($notification->verifyUrl);
            parse_str($url['query'] ?? '', $params);
            $plainToken = $params['token'] ?? null;

            return true;
        });

        $this->assertNotNull($plainToken);

        $this->postJson('/api/v1/auth/magic-link/verify', ['token' => $plainToken])->assertOk();
        $this->postJson('/api/v1/auth/magic-link/verify', ['token' => $plainToken])
            ->assertUnprocessable()
            ->assertJsonPath('errors.error_code', 'invalid_or_expired_link');
    }

    public function test_password_login_returns_token_and_merges_guest_cart(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $userCart = Cart::factory()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'status' => 'active',
        ]);

        CartItem::factory()->create([
            'cart_id' => $userCart->id,
            'product_variant_id' => null,
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
        ]);

        $guestCart = Cart::factory()->create([
            'user_id' => null,
            'session_id' => 'merge-me',
            'status' => 'active',
        ]);

        CartItem::factory()->create([
            'cart_id' => $guestCart->id,
            'product_variant_id' => null,
            'quantity' => 2,
            'unit_price' => 2000,
            'line_total' => 4000,
        ]);

        $response = $this->postJson('/api/v1/auth/password/login', [
            'email' => $user->email,
            'password' => 'password',
            'cart_token' => 'merge-me',
        ])->assertOk()
            ->assertJsonPath('data.authenticated', true);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertSame('merged', $guestCart->fresh()->status);
        $this->assertSame(5000, $userCart->fresh()->total_amount);
    }

    public function test_forgot_password_only_sends_reset_for_customer_accounts(): void
    {
        Notification::fake();

        $customer = User::factory()->create(['email' => 'customer@example.com']);
        $staff = User::factory()->create(['email' => 'staff@example.com']);
        $staff->assignRole(Role::findOrCreate('support_agent', 'web'));

        $this->postJson('/api/v1/auth/password/forgot', [
            'email' => $customer->email,
        ])->assertOk();

        $this->postJson('/api/v1/auth/password/forgot', [
            'email' => $staff->email,
        ])->assertOk();

        Notification::assertSentTo($customer, CustomerResetPasswordNotification::class);
        Notification::assertNotSentTo($staff, CustomerResetPasswordNotification::class);
    }
}
