<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '+233200000000',
                'status' => 'active',
                'password' => 'password123!',
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([
            Role::findByName('super_admin', 'web'),
        ]);
    }
}
