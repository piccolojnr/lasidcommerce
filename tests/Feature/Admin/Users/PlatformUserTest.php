<?php

namespace Tests\Feature\Admin\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformUserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage orders', 'guard_name' => 'web']);
        Role::findOrCreate('support_agent', 'web');

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage users');
    }

    public function test_platform_users_index_only_lists_internal_accounts(): void
    {
        $roleBasedStaff = User::factory()->create(['name' => 'Role Staff']);
        $roleBasedStaff->assignRole('support_agent');

        $permissionBasedStaff = User::factory()->create(['name' => 'Direct Staff']);
        $permissionBasedStaff->givePermissionTo('manage orders');

        $customer = User::factory()->create(['name' => 'Customer User']);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->where('users.data', fn ($data) => collect($data)->contains('id', $roleBasedStaff->id))
            ->where('users.data', fn ($data) => collect($data)->contains('id', $permissionBasedStaff->id))
            ->where('users.data', fn ($data) => collect($data)->doesntContain('id', $customer->id))
        );
    }

    public function test_platform_user_show_returns_not_found_for_customer_accounts(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $customer));

        $response->assertNotFound();
    }
}
