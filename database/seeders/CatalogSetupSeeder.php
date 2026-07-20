<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CatalogSetupSeeder extends Seeder
{
    /**
     * Absolute path to the bundled category image pack.
     * Filenames match category slugs exactly (e.g. dresses.jpg).
     */
    private string $imagePackPath;

    public function __construct()
    {
        $this->imagePackPath = base_path('backthred-category-image-pack/images');
    }

    public function run(): void
    {
        // ----------------------------------------------------------------
        // Fashion hierarchy
        // ----------------------------------------------------------------

        $fashion = $this->upsertCategory('fashion', [
            'name'        => 'Fashion',
            'description' => 'Starter category for apparel and accessories.',
            'sort_order'  => 1,
        ]);

        $womensFashion = $this->upsertCategory('womens-fashion', [
            'parent_id'   => $fashion->getKey(),
            'name'        => 'Women\'s Fashion',
            'description' => 'Curated apparel and dress-focused assortment for women.',
            'sort_order'  => 1,
        ]);

        $mensFashion = $this->upsertCategory('mens-fashion', [
            'parent_id'   => $fashion->getKey(),
            'name'        => 'Men\'s Fashion',
            'description' => 'Tailored menswear, everyday basics, and occasion pieces.',
            'sort_order'  => 2,
        ]);

        $kidsFashion = $this->upsertCategory('kids-fashion', [
            'parent_id'   => $fashion->getKey(),
            'name'        => 'Kids Fashion',
            'description' => 'Play-ready clothing and footwear for children.',
            'sort_order'  => 3,
        ]);

        $footwear = $this->upsertCategory('footwear', [
            'parent_id'   => $fashion->getKey(),
            'name'        => 'Footwear',
            'description' => 'Sneakers, sandals, heels, and occasion shoes.',
            'sort_order'  => 4,
        ]);

        $bags = $this->upsertCategory('bags', [
            'parent_id'   => $fashion->getKey(),
            'name'        => 'Bags',
            'description' => 'Work bags, totes, and compact carry goods.',
            'sort_order'  => 5,
        ]);

        $this->upsertCategory('accessories', [
            'parent_id'   => $fashion->getKey(),
            'name'        => 'Accessories',
            'description' => 'Belts, caps, sunglasses, scarves, and finishing touches.',
            'sort_order'  => 6,
        ]);

        // Women's sub-categories
        $this->upsertCategory('dresses', [
            'parent_id'   => $womensFashion->getKey(),
            'name'        => 'Dresses',
            'description' => 'Casual, occasion, and event-ready dresses.',
            'sort_order'  => 1,
        ]);

        $this->upsertCategory('tops', [
            'parent_id'   => $womensFashion->getKey(),
            'name'        => 'Tops',
            'description' => 'Blouses, tees, and elevated everyday tops.',
            'sort_order'  => 2,
        ]);

        // Men's sub-categories
        $this->upsertCategory('shirts', [
            'parent_id'   => $mensFashion->getKey(),
            'name'        => 'Shirts',
            'description' => 'Work shirts, casual shirts, and weekend staples.',
            'sort_order'  => 1,
        ]);

        $this->upsertCategory('trousers', [
            'parent_id'   => $mensFashion->getKey(),
            'name'        => 'Trousers',
            'description' => 'Smart-casual trousers and lightweight bottoms.',
            'sort_order'  => 2,
        ]);

        // Kids sub-categories
        $this->upsertCategory('school-wear', [
            'parent_id'   => $kidsFashion->getKey(),
            'name'        => 'School Wear',
            'description' => 'Uniform-ready looks and school-day basics.',
            'sort_order'  => 1,
        ]);

        $this->upsertCategory('play-wear', [
            'parent_id'   => $kidsFashion->getKey(),
            'name'        => 'Play Wear',
            'description' => 'Comfort-first clothing for active days.',
            'sort_order'  => 2,
        ]);

        // Footwear sub-categories
        $this->upsertCategory('sandals', [
            'parent_id'   => $footwear->getKey(),
            'name'        => 'Sandals',
            'description' => 'Warm-weather sandals and easy slip-ons.',
            'sort_order'  => 1,
        ]);

        $this->upsertCategory('sneakers', [
            'parent_id'   => $footwear->getKey(),
            'name'        => 'Sneakers',
            'description' => 'Low-top, running-inspired, and everyday sneakers.',
            'sort_order'  => 2,
        ]);

        // Bags sub-categories
        $this->upsertCategory('work-bags', [
            'parent_id'   => $bags->getKey(),
            'name'        => 'Work Bags',
            'description' => 'Structured carry goods for office and commute use.',
            'sort_order'  => 1,
        ]);

        $this->upsertCategory('crossbody-bags', [
            'parent_id'   => $bags->getKey(),
            'name'        => 'Crossbody Bags',
            'description' => 'Compact bags for quick errands and day trips.',
            'sort_order'  => 2,
        ]);

        // ----------------------------------------------------------------
        // Beauty hierarchy
        // ----------------------------------------------------------------

        $beauty = $this->upsertCategory('beauty', [
            'name'        => 'Beauty',
            'description' => 'Skin, scent, and daily care essentials.',
            'sort_order'  => 2,
        ]);

        $this->upsertCategory('fragrance', [
            'parent_id'   => $beauty->getKey(),
            'name'        => 'Fragrance',
            'description' => 'Perfumes and body mists for gifting and personal use.',
            'sort_order'  => 1,
        ]);

        $this->upsertCategory('skin-care', [
            'parent_id'   => $beauty->getKey(),
            'name'        => 'Skin Care',
            'description' => 'Daily skin prep and body care routines.',
            'sort_order'  => 2,
        ]);

        // ----------------------------------------------------------------
        // Home & Living hierarchy
        // ----------------------------------------------------------------

        $home = $this->upsertCategory('home-living', [
            'name'        => 'Home & Living',
            'description' => 'Home utility, decor, and soft-furnishing essentials.',
            'sort_order'  => 3,
        ]);

        $this->upsertCategory('decor', [
            'parent_id'   => $home->getKey(),
            'name'        => 'Decor',
            'description' => 'Accent decor and atmosphere-building pieces.',
            'sort_order'  => 1,
        ]);

        $this->upsertCategory('bed-bath', [
            'parent_id'   => $home->getKey(),
            'name'        => 'Bed & Bath',
            'description' => 'Soft essentials for sleeping and bathroom comfort.',
            'sort_order'  => 2,
        ]);

        // ----------------------------------------------------------------
        // Brands
        // ----------------------------------------------------------------

        Brand::query()->updateOrCreate(
            ['slug' => 'lasid-basics'],
            [
                'name'        => 'Lasid Basics',
                'description' => 'In-house essentials for wardrobe and lifestyle basics.',
                'is_active'   => true,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'urban-step'],
            [
                'name'        => 'Urban Step',
                'description' => 'Starter footwear brand seed.',
                'is_active'   => true,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'adma-studio'],
            [
                'name'        => 'ADMA Studio',
                'description' => 'Modern fashion-led brand for elevated ready-to-wear pieces.',
                'is_active'   => true,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'northline-home'],
            [
                'name'        => 'Northline Home',
                'description' => 'Home utility and decor brand for practical living essentials.',
                'is_active'   => true,
            ]
        );

        Brand::query()->updateOrCreate(
            ['slug' => 'gold-coast-care'],
            [
                'name'        => 'Gold Coast Care',
                'description' => 'Body care and fragrance line for premium daily routines.',
                'is_active'   => true,
            ]
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Upsert a category by slug, attach its image from the local pack if a
     * matching file exists, and return the model.
     *
     * The Category media collection is declared as singleFile(), so calling
     * addMediaFromPath() on an existing category simply replaces the current
     * image rather than adding a duplicate.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function upsertCategory(string $slug, array $attributes): Category
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => $slug],
            array_merge($attributes, ['is_active' => true]),
        );

        $this->attachCategoryImage($category, $slug);

        return $category;
    }

    /**
     * Attach the corresponding image from the local pack to a category.
     * Silently skips if no matching file is found so the seeder stays safe
     * even if the image pack is not present (e.g. CI environments).
     */
    private function attachCategoryImage(Category $category, string $slug): void
    {
        $path = $this->imagePackPath . DIRECTORY_SEPARATOR . $slug . '.jpg';

        if (! file_exists($path)) {
            return;
        }

        // addMedia() accepts a local file path and returns a FileAdder.
        // singleFile() on the collection handles replacement on re-runs automatically.
        $category
            ->addMedia($path)
            ->usingFileName($slug . '.jpg')
            ->usingName($category->name)
            ->withCustomProperties(['seeded' => true])
            ->preservingOriginal()
            ->toMediaCollection('images');
    }
}
