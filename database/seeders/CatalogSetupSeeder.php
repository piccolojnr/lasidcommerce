<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CatalogSetupSeeder extends Seeder
{
    public function run(): void
    {
        $fashion = Category::query()->updateOrCreate(
            ['slug' => 'fashion'],
            [
                'name' => 'Fashion',
                'description' => 'Starter category for apparel and accessories.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'footwear'],
            [
                'parent_id' => $fashion->getKey(),
                'name' => 'Footwear',
                'description' => 'Starter category for shoes and sandals.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'accessories'],
            [
                'parent_id' => $fashion->getKey(),
                'name' => 'Accessories',
                'description' => 'Starter category for add-ons and finishing touches.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'lasid-basics'],
            [
                'name' => 'Lasid Basics',
                'description' => 'Starter in-house essentials brand.',
                'is_active' => true,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'urban-step'],
            [
                'name' => 'Urban Step',
                'description' => 'Starter footwear brand seed.',
                'is_active' => true,
            ]
        );
    }
}
