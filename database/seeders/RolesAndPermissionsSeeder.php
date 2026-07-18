<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'super_admin',
            'catalog_manager',
            'order_manager',
            'support_agent',
        ];

        $permissions = [
            'view admin dashboard',
            'manage categories',
            'manage brands',
            'manage products',
            'manage orders',
            'manage shipments',
            'manage coupons',
            'manage users',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        Role::findByName('super_admin', 'web')->syncPermissions($permissions);
        Role::findByName('catalog_manager', 'web')->syncPermissions([
            'view admin dashboard',
            'manage categories',
            'manage brands',
            'manage products',
        ]);
        Role::findByName('order_manager', 'web')->syncPermissions([
            'view admin dashboard',
            'manage orders',
            'manage shipments',
            'manage coupons',
        ]);
        Role::findByName('support_agent', 'web')->syncPermissions([
            'view admin dashboard',
            'manage orders',
        ]);
    }
}
