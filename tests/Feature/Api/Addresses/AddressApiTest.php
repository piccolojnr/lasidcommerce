<?php

namespace Tests\Feature\Api\Addresses;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'type'           => 'shipping',
            'name'           => 'Kwame Mensah',
            'phone'          => '+233244000001',
            'country'        => 'Ghana',
            'region'         => 'Greater Accra',
            'city'           => 'Accra',
            'address_line_1' => '12 Independence Ave',
            'is_default'     => false,
        ], $overrides);
    }

    // --- GET /api/v1/addresses ---

    public function test_list_returns_own_addresses(): void
    {
        $user  = $this->user();
        $other = $this->user();

        Address::factory()->create(['user_id' => $user->id]);
        Address::factory()->create(['user_id' => $user->id]);
        Address::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAsCustomer($user)->getJson('/api/v1/addresses');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_list_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/addresses');

        $response->assertUnauthorized();
    }

    // --- POST /api/v1/addresses ---

    public function test_create_address(): void
    {
        $user = $this->user();

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/addresses', $this->addressPayload());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'shipping')
            ->assertJsonPath('data.name', 'Kwame Mensah')
            ->assertJsonPath('data.city', 'Accra');

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'city'    => 'Accra',
        ]);
    }

    public function test_create_with_is_default_true_stores_default(): void
    {
        $user = $this->user();

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/addresses', $this->addressPayload(['is_default' => true]));

        $response->assertCreated()
            ->assertJsonPath('data.is_default', true);
    }

    public function test_create_validates_type(): void
    {
        $user = $this->user();

        $response = $this->actingAsCustomer($user)->postJson('/api/v1/addresses', $this->addressPayload(['type' => 'invalid']));

        $response->assertUnprocessable();
    }

    public function test_create_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/addresses', $this->addressPayload());

        $response->assertUnauthorized();
    }

    // --- PATCH /api/v1/addresses/{address} ---

    public function test_update_own_address(): void
    {
        $user    = $this->user();
        $address = Address::factory()->create(['user_id' => $user->id, 'city' => 'Kumasi']);

        $response = $this->actingAsCustomer($user)->patchJson(
            "/api/v1/addresses/{$address->id}",
            ['city' => 'Tamale'],
        );

        $response->assertOk()
            ->assertJsonPath('data.city', 'Tamale');

        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'city' => 'Tamale']);
    }

    public function test_cannot_update_another_users_address(): void
    {
        $user    = $this->user();
        $other   = $this->user();
        $address = Address::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAsCustomer($user)->patchJson(
            "/api/v1/addresses/{$address->id}",
            ['city' => 'Tamale'],
        );

        $response->assertNotFound();
    }

    // --- DELETE /api/v1/addresses/{address} ---

    public function test_delete_own_address(): void
    {
        $user    = $this->user();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsCustomer($user)->deleteJson("/api/v1/addresses/{$address->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_cannot_delete_another_users_address(): void
    {
        $user    = $this->user();
        $other   = $this->user();
        $address = Address::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAsCustomer($user)->deleteJson("/api/v1/addresses/{$address->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    // --- PATCH /api/v1/addresses/{address}/default ---

    public function test_set_default_marks_address_as_default(): void
    {
        $user    = $this->user();
        $address = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

        $response = $this->actingAsCustomer($user)->patchJson("/api/v1/addresses/{$address->id}/default");

        $response->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'is_default' => true]);
    }

    public function test_set_default_unsets_previous_default(): void
    {
        $user    = $this->user();
        $first   = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
        $second  = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

        $this->actingAsCustomer($user)->patchJson("/api/v1/addresses/{$second->id}/default");

        $this->assertDatabaseHas('addresses', ['id' => $first->id,  'is_default' => false]);
        $this->assertDatabaseHas('addresses', ['id' => $second->id, 'is_default' => true]);
    }

    public function test_cannot_set_default_on_another_users_address(): void
    {
        $user    = $this->user();
        $other   = $this->user();
        $address = Address::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAsCustomer($user)->patchJson("/api/v1/addresses/{$address->id}/default");

        $response->assertNotFound();
    }
}

