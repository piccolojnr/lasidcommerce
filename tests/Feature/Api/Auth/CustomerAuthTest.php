<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Notifications\CustomerMagicLinkNotification;
use Illuminate\Auth\Notifications\ResetPassword;
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

    public function test_csrf_cookie_endpoint_sets_storefront_csrf_cookie(): void
    {
        $response = $this->getJson('/api/v1/auth/csrf-cookie');

        $response->assertOk()
            ->assertJsonPath('data.csrf_cookie', config('storefront.csrf_cookie'))
            ->assertJsonPath('data.csrf_header', config('storefront.csrf_header'))
            ->assertCookie(config('storefront.csrf_cookie'))
            ->assertCookie(config('storefront.session_cookie'));
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
            $this->assertStringContainsString('/api/v1/auth/magic-link/verify', $notification->verifyUrl);

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

    public function test_magic_link_verification_creates_customer_logs_in_and_adopts_guest_cart(): void
    {
        Notification::fake();

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

        $verifyUrl = null;

        Notification::assertSentOnDemand(CustomerMagicLinkNotification::class, function (CustomerMagicLinkNotification $notification) use (&$verifyUrl) {
            $verifyUrl = $notification->verifyUrl;

            return true;
        });

        $response = $this->get($verifyUrl);

        $response->assertRedirect('http://shop.example.test/account/orders');
        $this->assertAuthenticated('customer');

        $user = User::where('email', 'new-customer@example.com')->firstOrFail();
        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('', $user->name);

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

        $verifyUrl = null;

        Notification::assertSentOnDemand(CustomerMagicLinkNotification::class, function (CustomerMagicLinkNotification $notification) use (&$verifyUrl) {
            $verifyUrl = $notification->verifyUrl;

            return true;
        });

        $this->get($verifyUrl)->assertRedirect('http://shop.example.test/account');
        $this->get($verifyUrl)->assertRedirect('http://shop.example.test/auth?auth_error=invalid_or_expired_link');
    }

    public function test_password_login_merges_guest_cart_into_existing_customer_cart(): void
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

        $this->postJson('/api/v1/auth/password/login', [
            'email' => $user->email,
            'password' => 'password',
            'cart_token' => 'merge-me',
        ])->assertOk()
            ->assertJsonPath('data.authenticated', true);

        $this->assertAuthenticated('customer');
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

        Notification::assertSentTo($customer, ResetPassword::class);
        Notification::assertNotSentTo($staff, ResetPassword::class);
    }
}
