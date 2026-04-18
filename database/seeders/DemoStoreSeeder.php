<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoUsersSeeder::class,
            DemoCatalogSeeder::class,
            DemoOrdersSeeder::class,
        ]);
    }
}
