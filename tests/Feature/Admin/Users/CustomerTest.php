<?php

namespace Tests\Feature\Admin\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        Role::findOrCreate('support_agent', 'web');

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage users');
    }

    public function test_customers_index_only_lists_non_staff_accounts(): void
    {
        $customer = User::factory()->create(['name' => 'Customer User']);
        $staff = User::factory()->create(['name' => 'Staff User']);
        $staff->assignRole('support_agent');

        $response = $this->actingAs($this->admin)->get(route('admin.customers.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/customers/index')
            ->where('customers.data', fn ($data) => collect($data)->contains('id', $customer->id))
            ->where('customers.data', fn ($data) => collect($data)->doesntContain('id', $staff->id))
        );
    }

    public function test_customer_show_returns_not_found_for_platform_users(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('support_agent');

        $response = $this->actingAs($this->admin)->get(route('admin.customers.show', $staff));

        $response->assertNotFound();
    }
}
