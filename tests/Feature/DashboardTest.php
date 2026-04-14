<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'view admin dashboard', 'guard_name' => 'web']);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('admin.dashboard.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_dashboard_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard.index'));

        $response->assertForbidden();
    }

    public function test_authorized_user_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view admin dashboard');

        $response = $this->actingAs($user)->get(route('admin.dashboard.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/dashboard/index')
            ->has('overview')
            ->has('recent_orders')
            ->has('recent_payments')
            ->has('recent_shipments')
        );
    }
}
