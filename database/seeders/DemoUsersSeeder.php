<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $staffUsers = [
            [
                'email' => 'catalog.manager@example.com',
                'name' => 'Catalog Manager',
                'phone' => '+233200000101',
                'role' => 'catalog_manager',
            ],
            [
                'email' => 'orders.manager@example.com',
                'name' => 'Orders Manager',
                'phone' => '+233200000102',
                'role' => 'order_manager',
            ],
            [
                'email' => 'support.agent@example.com',
                'name' => 'Support Agent',
                'phone' => '+233200000103',
                'role' => 'support_agent',
            ],
        ];

        foreach ($staffUsers as $definition) {
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'phone' => $definition['phone'],
                    'status' => 'active',
                    'password' => 'password123!',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([
                Role::findByName($definition['role'], 'web'),
            ]);
        }

        $customers = [
            [
                'email' => 'customer.ama@example.com',
                'name' => 'Ama Mensah',
                'phone' => '+233240000111',
                'city' => 'Accra',
                'region' => 'Greater Accra',
                'address_line_1' => '18 Osu Ringway',
                'landmark' => 'Near Oxford Street',
            ],
            [
                'email' => 'customer.kojo@example.com',
                'name' => 'Kojo Asare',
                'phone' => '+233540000112',
                'city' => 'Kumasi',
                'region' => 'Ashanti',
                'address_line_1' => '45 Adum High Street',
                'landmark' => 'Opposite Central Market',
            ],
            [
                'email' => 'customer.efua@example.com',
                'name' => 'Efua Nkrumah',
                'phone' => '+233270000113',
                'city' => 'Accra',
                'region' => 'Greater Accra',
                'address_line_1' => '7 East Legon Avenue',
                'landmark' => 'Adjiringanor Junction',
            ],
        ];

        foreach ($customers as $definition) {
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'phone' => $definition['phone'],
                    'status' => 'active',
                    'password' => 'password123!',
                    'email_verified_at' => now(),
                ]
            );

            Address::query()->updateOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'type' => 'shipping',
                    'address_line_1' => $definition['address_line_1'],
                ],
                [
                    'name' => $definition['name'],
                    'phone' => $definition['phone'],
                    'country' => 'Ghana',
                    'region' => $definition['region'],
                    'city' => $definition['city'],
                    'district' => null,
                    'address_line_2' => null,
                    'landmark' => $definition['landmark'],
                    'postal_code' => null,
                    'is_default' => true,
                ]
            );

            Address::query()->updateOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'type' => 'billing',
                    'address_line_1' => $definition['address_line_1'],
                ],
                [
                    'name' => $definition['name'],
                    'phone' => $definition['phone'],
                    'country' => 'Ghana',
                    'region' => $definition['region'],
                    'city' => $definition['city'],
                    'district' => null,
                    'address_line_2' => null,
                    'landmark' => $definition['landmark'],
                    'postal_code' => null,
                    'is_default' => true,
                ]
            );
        }
    }
}
