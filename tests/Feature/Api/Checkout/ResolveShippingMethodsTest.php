<?php

namespace Tests\Feature\Api\Checkout;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveShippingMethodsTest extends TestCase
{
    use RefreshDatabase;

    private function zone(string $countryName = 'GH'): ShippingZone
    {
        $zone = ShippingZone::factory()->create([
            'is_active' => true,
            'code' => 'ghana',
        ]);

        ShippingZoneArea::factory()->create([
            'shipping_zone_id' => $zone->id,
            'area_type' => 'country',
            'area_name' => $countryName,
        ]);

        return $zone;
    }

    private function method(ShippingZone $zone, int $fee = 1000, bool $isActive = true): ShippingMethod
    {
        $method = ShippingMethod::factory()->create([
            'flat_rate_amount' => $fee,
            'is_active' => $isActive,
        ]);

        $zone->shippingMethods()->attach($method);

        return $method;
    }

    public function test_resolve_returns_zone_and_active_methods(): void
    {
        $zone = $this->zone('GH');
        $activeMethod = $this->method($zone, 1500, true);
        $this->method($zone, 2500, false);

        $response = $this->postJson('/api/v1/checkout/shipping-methods/resolve', [
            'country' => 'GH',
            'city' => 'Accra',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.shipping_zone.id', $zone->id)
            ->assertJsonPath('data.shipping_methods.0.id', $activeMethod->id)
            ->assertJsonPath('data.shipping_methods.0.shipping_amount', 1500);

        $this->assertCount(1, $response->json('data.shipping_methods'));
    }

    public function test_resolve_returns_empty_result_when_no_zone_matches(): void
    {
        $response = $this->postJson('/api/v1/checkout/shipping-methods/resolve', [
            'country' => 'NG',
            'city' => 'Lagos',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.shipping_zone', null);

        $this->assertSame([], $response->json('data.shipping_methods'));
    }
}
