<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_default_notification_preferences(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => null,
        ]);

        $response = $this->actingAsCustomer($user)->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonPath('data.notification_preferences.auth_magic_link', true)
            ->assertJsonPath('data.notification_preferences.orders_placed', true)
            ->assertJsonPath('data.notification_preferences.payments_received', true);
    }

    public function test_update_can_store_notification_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsCustomer($user)->patchJson('/api/v1/profile', [
            'notification_preferences' => [
                'orders_placed' => false,
                'payments_received' => false,
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.notification_preferences.orders_placed', false)
            ->assertJsonPath('data.notification_preferences.payments_received', false)
            ->assertJsonPath('data.notification_preferences.auth_magic_link', true);

        $this->assertSame(false, $user->fresh()->notification_preferences['orders_placed']);
        $this->assertSame(false, $user->fresh()->notification_preferences['payments_received']);
    }

    public function test_update_merges_notification_preferences_without_resetting_other_values(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'orders_placed' => false,
                'payments_received' => true,
            ],
        ]);

        $response = $this->actingAsCustomer($user)->patchJson('/api/v1/profile', [
            'notification_preferences' => [
                'payments_received' => false,
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.notification_preferences.orders_placed', false)
            ->assertJsonPath('data.notification_preferences.payments_received', false);
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
        $this->patchJson('/api/v1/profile', [
            'notification_preferences' => ['orders_placed' => false],
        ])->assertUnauthorized();
    }
}
