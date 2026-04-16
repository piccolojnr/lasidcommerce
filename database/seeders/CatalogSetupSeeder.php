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

        $womensFashion = Category::query()->updateOrCreate(
            ['slug' => 'womens-fashion'],
            [
                'parent_id' => $fashion->getKey(),
                'name' => 'Women\'s Fashion',
                'description' => 'Curated apparel and dress-focused assortment for women.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $mensFashion = Category::query()->updateOrCreate(
            ['slug' => 'mens-fashion'],
            [
                'parent_id' => $fashion->getKey(),
                'name' => 'Men\'s Fashion',
                'description' => 'Tailored menswear, everyday basics, and occasion pieces.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $kidsFashion = Category::query()->updateOrCreate(
            ['slug' => 'kids-fashion'],
            [
                'parent_id' => $fashion->getKey(),
                'name' => 'Kids Fashion',
                'description' => 'Play-ready clothing and footwear for children.',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $footwear = Category::query()->updateOrCreate(
            ['slug' => 'footwear'],
            [
                'parent_id' => $fashion->getKey(),
                'name' => 'Footwear',
                'description' => 'Sneakers, sandals, heels, and occasion shoes.',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        $bags = Category::query()->updateOrCreate(
            ['slug' => 'bags'],
            [
                'parent_id' => $fashion->getKey(),
                'name' => 'Bags',
                'description' => 'Work bags, totes, and compact carry goods.',
                'is_active' => true,
                'sort_order' => 5,
            ]
        );

        $accessories = Category::query()->updateOrCreate(
            ['slug' => 'accessories'],
            [
                'parent_id' => $fashion->getKey(),
                'name' => 'Accessories',
                'description' => 'Belts, caps, sunglasses, scarves, and finishing touches.',
                'is_active' => true,
                'sort_order' => 6,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'dresses'],
            [
                'parent_id' => $womensFashion->getKey(),
                'name' => 'Dresses',
                'description' => 'Casual, occasion, and event-ready dresses.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'tops'],
            [
                'parent_id' => $womensFashion->getKey(),
                'name' => 'Tops',
                'description' => 'Blouses, tees, and elevated everyday tops.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'shirts'],
            [
                'parent_id' => $mensFashion->getKey(),
                'name' => 'Shirts',
                'description' => 'Work shirts, casual shirts, and weekend staples.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'trousers'],
            [
                'parent_id' => $mensFashion->getKey(),
                'name' => 'Trousers',
                'description' => 'Smart-casual trousers and lightweight bottoms.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'school-wear'],
            [
                'parent_id' => $kidsFashion->getKey(),
                'name' => 'School Wear',
                'description' => 'Uniform-ready looks and school-day basics.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'play-wear'],
            [
                'parent_id' => $kidsFashion->getKey(),
                'name' => 'Play Wear',
                'description' => 'Comfort-first clothing for active days.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'sandals'],
            [
                'parent_id' => $footwear->getKey(),
                'name' => 'Sandals',
                'description' => 'Warm-weather sandals and easy slip-ons.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'sneakers'],
            [
                'parent_id' => $footwear->getKey(),
                'name' => 'Sneakers',
                'description' => 'Low-top, running-inspired, and everyday sneakers.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'work-bags'],
            [
                'parent_id' => $bags->getKey(),
                'name' => 'Work Bags',
                'description' => 'Structured carry goods for office and commute use.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'crossbody-bags'],
            [
                'parent_id' => $bags->getKey(),
                'name' => 'Crossbody Bags',
                'description' => 'Compact bags for quick errands and day trips.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $beauty = Category::query()->updateOrCreate(
            ['slug' => 'beauty'],
            [
                'name' => 'Beauty',
                'description' => 'Skin, scent, and daily care essentials.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'fragrance'],
            [
                'parent_id' => $beauty->getKey(),
                'name' => 'Fragrance',
                'description' => 'Perfumes and body mists for gifting and personal use.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'skin-care'],
            [
                'parent_id' => $beauty->getKey(),
                'name' => 'Skin Care',
                'description' => 'Daily skin prep and body care routines.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $home = Category::query()->updateOrCreate(
            ['slug' => 'home-living'],
            [
                'name' => 'Home & Living',
                'description' => 'Home utility, decor, and soft-furnishing essentials.',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'decor'],
            [
                'parent_id' => $home->getKey(),
                'name' => 'Decor',
                'description' => 'Accent decor and atmosphere-building pieces.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'bed-bath'],
            [
                'parent_id' => $home->getKey(),
                'name' => 'Bed & Bath',
                'description' => 'Soft essentials for sleeping and bathroom comfort.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'lasid-basics'],
            [
                'name' => 'Lasid Basics',
                'description' => 'In-house essentials for wardrobe and lifestyle basics.',
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

        Brand::query()->updateOrCreate(
            ['slug' => 'adma-studio'],
            [
                'name' => 'ADMA Studio',
                'description' => 'Modern fashion-led brand for elevated ready-to-wear pieces.',
                'is_active' => true,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'northline-home'],
            [
                'name' => 'Northline Home',
                'description' => 'Home utility and decor brand for practical living essentials.',
                'is_active' => true,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'gold-coast-care'],
            [
                'name' => 'Gold Coast Care',
                'description' => 'Body care and fragrance line for premium daily routines.',
                'is_active' => true,
            ]
        );
    }
}
