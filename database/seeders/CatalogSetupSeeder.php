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
            'name' => 'Fashion',
            'description' => 'Clothing, footwear, bags, jewelry, and fashion accessories.',
            'sort_order' => 1,
        ]);

        $womensFashion = $this->upsertCategory('womens-fashion', [
            'parent_id' => $fashion->getKey(),
            'name' => 'Women\'s Fashion',
            'description' => 'Clothing and apparel designed for women.',
            'sort_order' => 1,
        ]);

        $mensFashion = $this->upsertCategory('mens-fashion', [
            'parent_id' => $fashion->getKey(),
            'name' => 'Men\'s Fashion',
            'description' => 'Clothing and apparel designed for men.',
            'sort_order' => 2,
        ]);

        $kidsFashion = $this->upsertCategory('kids-fashion', [
            'parent_id' => $fashion->getKey(),
            'name' => 'Kids Fashion',
            'description' => 'Clothing and apparel for children.',
            'sort_order' => 3,
        ]);

        $footwear = $this->upsertCategory('footwear', [
            'parent_id' => $fashion->getKey(),
            'name' => 'Footwear',
            'description' => 'Shoes, sandals, sneakers, boots, and footwear accessories.',
            'sort_order' => 4,
        ]);

        $bags = $this->upsertCategory('bags', [
            'parent_id' => $fashion->getKey(),
            'name' => 'Bags & Luggage',
            'description' => 'Handbags, travel bags, luggage, wallets, and carrying accessories.',
            'sort_order' => 5,
        ]);

        $jewelryWatches = $this->upsertCategory('jewelry-watches', [
            'parent_id' => $fashion->getKey(),
            'name' => 'Jewelry & Watches',
            'description' => 'Fashion jewelry, fine jewelry, watches, and related accessories.',
            'sort_order' => 6,
        ]);

        $this->upsertCategory('underwear-sleepwear', [
            'parent_id' => $fashion->getKey(),
            'name' => 'Underwear & Sleepwear',
            'description' => 'Underwear, socks, hosiery, lingerie, and sleepwear.',
            'sort_order' => 7,
        ]);

        $this->upsertCategory('accessories', [
            'parent_id' => $fashion->getKey(),
            'name' => 'Fashion Accessories',
            'description' => 'Belts, hats, glasses, scarves, gloves, hair accessories, and keychains.',
            'sort_order' => 8,
        ]);

        // Women's Fashion subcategories

        $this->upsertCategory('dresses', [
            'parent_id' => $womensFashion->getKey(),
            'name' => 'Dresses',
            'description' => 'Casual, formal, occasion, and event-ready dresses.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('tops', [
            'parent_id' => $womensFashion->getKey(),
            'name' => 'Tops',
            'description' => 'Blouses, shirts, T-shirts, knitwear, and everyday tops.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('womens-bottoms', [
            'parent_id' => $womensFashion->getKey(),
            'name' => 'Bottoms',
            'description' => 'Trousers, jeans, skirts, shorts, and other women\'s bottoms.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('womens-outerwear', [
            'parent_id' => $womensFashion->getKey(),
            'name' => 'Outerwear',
            'description' => 'Jackets, coats, cardigans, and other women\'s outerwear.',
            'sort_order' => 4,
        ]);

        // Men's Fashion subcategories

        $this->upsertCategory('shirts', [
            'parent_id' => $mensFashion->getKey(),
            'name' => 'Shirts',
            'description' => 'Formal shirts, casual shirts, polos, and everyday shirts.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('trousers', [
            'parent_id' => $mensFashion->getKey(),
            'name' => 'Trousers',
            'description' => 'Trousers, jeans, shorts, and other men\'s bottoms.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('mens-outerwear', [
            'parent_id' => $mensFashion->getKey(),
            'name' => 'Outerwear',
            'description' => 'Jackets, coats, sweatshirts, and other men\'s outerwear.',
            'sort_order' => 3,
        ]);

        // Kids Fashion subcategories

        $this->upsertCategory('school-wear', [
            'parent_id' => $kidsFashion->getKey(),
            'name' => 'School Wear',
            'description' => 'Uniform-ready clothing and school-day basics.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('play-wear', [
            'parent_id' => $kidsFashion->getKey(),
            'name' => 'Play Wear',
            'description' => 'Comfortable everyday clothing for active children.',
            'sort_order' => 2,
        ]);

        // Footwear subcategories

        $this->upsertCategory('sandals', [
            'parent_id' => $footwear->getKey(),
            'name' => 'Sandals',
            'description' => 'Flat sandals, wedge sandals, slides, and slip-ons.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('sneakers', [
            'parent_id' => $footwear->getKey(),
            'name' => 'Sneakers',
            'description' => 'Casual, athletic, running-inspired, and everyday sneakers.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('boots', [
            'parent_id' => $footwear->getKey(),
            'name' => 'Boots',
            'description' => 'Ankle boots, fashion boots, and outdoor boots.',
            'sort_order' => 3,
        ]);

        // Bags & Luggage subcategories

        $this->upsertCategory('work-bags', [
            'parent_id' => $bags->getKey(),
            'name' => 'Work Bags',
            'description' => 'Structured bags for office, school, and commuting.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('crossbody-bags', [
            'parent_id' => $bags->getKey(),
            'name' => 'Crossbody Bags',
            'description' => 'Compact crossbody and shoulder bags for everyday use.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('travel-luggage', [
            'parent_id' => $bags->getKey(),
            'name' => 'Travel Bags & Luggage',
            'description' => 'Suitcases, luggage sets, duffel bags, and travel storage.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('wallets-cardholders', [
            'parent_id' => $bags->getKey(),
            'name' => 'Wallets & Cardholders',
            'description' => 'Wallets, purses, coin holders, and cardholders.',
            'sort_order' => 4,
        ]);

        // Jewelry & Watches subcategories

        $this->upsertCategory('jewelry', [
            'parent_id' => $jewelryWatches->getKey(),
            'name' => 'Jewelry',
            'description' => 'Rings, necklaces, bracelets, earrings, pendants, and jewelry sets.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('watches', [
            'parent_id' => $jewelryWatches->getKey(),
            'name' => 'Watches',
            'description' => 'Fashion watches, quartz watches, watch sets, and watch accessories.',
            'sort_order' => 2,
        ]);

        // ----------------------------------------------------------------
// Beauty hierarchy
// ----------------------------------------------------------------

        $beauty = $this->upsertCategory('beauty', [
            'name' => 'Beauty',
            'description' => 'Beauty, grooming, skin, hair, fragrance, and personal-care products.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('fragrance', [
            'parent_id' => $beauty->getKey(),
            'name' => 'Fragrance',
            'description' => 'Perfumes, body mists, fragrances, and aromatherapy products.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('skin-care', [
            'parent_id' => $beauty->getKey(),
            'name' => 'Skin Care',
            'description' => 'Cleansers, moisturizers, masks, serums, and skin treatments.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('makeup-nails', [
            'parent_id' => $beauty->getKey(),
            'name' => 'Makeup & Nails',
            'description' => 'Cosmetics, false eyelashes, press-on nails, and nail-care products.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('hair-care-wigs', [
            'parent_id' => $beauty->getKey(),
            'name' => 'Hair Care & Wigs',
            'description' => 'Hair products, wigs, extensions, weaves, and styling accessories.',
            'sort_order' => 4,
        ]);

        $this->upsertCategory('personal-care', [
            'parent_id' => $beauty->getKey(),
            'name' => 'Personal Care',
            'description' => 'Oral care, body care, grooming, and everyday personal-care products.',
            'sort_order' => 5,
        ]);

        $this->upsertCategory('beauty-tools-appliances', [
            'parent_id' => $beauty->getKey(),
            'name' => 'Beauty Tools & Appliances',
            'description' => 'Makeup tools, brushes, hair appliances, nail equipment, and organizers.',
            'sort_order' => 6,
        ]);

        // ----------------------------------------------------------------
// Home & Living hierarchy
// ----------------------------------------------------------------

        $home = $this->upsertCategory('home-living', [
            'name' => 'Home & Living',
            'description' => 'Home essentials, furniture, decor, kitchenware, tools, and household products.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('decor', [
            'parent_id' => $home->getKey(),
            'name' => 'Home Decor',
            'description' => 'Decorative accents, mirrors, wall decor, candles, ornaments, and artwork.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('bed-bath', [
            'parent_id' => $home->getKey(),
            'name' => 'Bed, Bath & Home Textiles',
            'description' => 'Bedding, towels, blankets, cushions, curtains, and bathroom textiles.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('kitchen-dining', [
            'parent_id' => $home->getKey(),
            'name' => 'Kitchen & Dining',
            'description' => 'Cookware, bakeware, tableware, drinkware, and kitchen tools.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('storage-organization', [
            'parent_id' => $home->getKey(),
            'name' => 'Storage & Organization',
            'description' => 'Storage boxes, organizers, shelves, racks, hangers, and containers.',
            'sort_order' => 4,
        ]);

        $this->upsertCategory('cleaning-household', [
            'parent_id' => $home->getKey(),
            'name' => 'Cleaning & Household',
            'description' => 'Cleaning tools, household chemicals, tissues, wipes, and daily essentials.',
            'sort_order' => 5,
        ]);

        $this->upsertCategory('furniture', [
            'parent_id' => $home->getKey(),
            'name' => 'Furniture',
            'description' => 'Living room, bedroom, dining, bathroom, entryway, and office furniture.',
            'sort_order' => 6,
        ]);

        $this->upsertCategory('home-appliances', [
            'parent_id' => $home->getKey(),
            'name' => 'Home Appliances',
            'description' => 'Kitchen appliances, cooking appliances, kettles, blenders, and related products.',
            'sort_order' => 7,
        ]);

        $this->upsertCategory('tools-hardware-lighting', [
            'parent_id' => $home->getKey(),
            'name' => 'Tools, Hardware & Lighting',
            'description' => 'Hand tools, power tools, hardware, electrical supplies, lamps, and lighting.',
            'sort_order' => 8,
        ]);

        $this->upsertCategory('garden-outdoor', [
            'parent_id' => $home->getKey(),
            'name' => 'Garden & Outdoor',
            'description' => 'Gardening tools, planters, watering equipment, outdoor furniture, and decor.',
            'sort_order' => 9,
        ]);

        $this->upsertCategory('party-event-supplies', [
            'parent_id' => $home->getKey(),
            'name' => 'Party & Event Supplies',
            'description' => 'Party decorations, balloons, tableware, invitations, favors, and event supplies.',
            'sort_order' => 10,
        ]);

        $this->upsertCategory('arts-crafts-sewing', [
            'parent_id' => $home->getKey(),
            'name' => 'Arts, Crafts & Sewing',
            'description' => 'Craft materials, sewing supplies, painting tools, ribbons, molds, and DIY kits.',
            'sort_order' => 11,
        ]);

        // ----------------------------------------------------------------
// Electronics hierarchy
// ----------------------------------------------------------------

        $electronics = $this->upsertCategory('electronics', [
            'name' => 'Electronics',
            'description' => 'Consumer electronics, phones, computers, audio, cameras, and gaming products.',
            'sort_order' => 4,
        ]);

        $this->upsertCategory('phones-accessories', [
            'parent_id' => $electronics->getKey(),
            'name' => 'Phones & Accessories',
            'description' => 'Phone cases, chargers, cables, mounts, holders, and mobile accessories.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('computers-office-electronics', [
            'parent_id' => $electronics->getKey(),
            'name' => 'Computers & Office Electronics',
            'description' => 'Laptops, networking equipment, projectors, and computer accessories.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('audio-wearables', [
            'parent_id' => $electronics->getKey(),
            'name' => 'Audio & Wearables',
            'description' => 'Headphones, earphones, wireless earbuds, smart watches, and wearable devices.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('cameras-photography', [
            'parent_id' => $electronics->getKey(),
            'name' => 'Cameras & Photography',
            'description' => 'Camera equipment, photography accessories, tripods, and studio products.',
            'sort_order' => 4,
        ]);

        $this->upsertCategory('gaming-consoles', [
            'parent_id' => $electronics->getKey(),
            'name' => 'Gaming & Consoles',
            'description' => 'Video games, consoles, gaming accessories, and entertainment devices.',
            'sort_order' => 5,
        ]);

        // ----------------------------------------------------------------
// Sports & Outdoors hierarchy
// ----------------------------------------------------------------

        $sports = $this->upsertCategory('sports-outdoors', [
            'name' => 'Sports & Outdoors',
            'description' => 'Fitness, sports equipment, outdoor recreation, and activity accessories.',
            'sort_order' => 5,
        ]);

        $this->upsertCategory('fitness-training', [
            'parent_id' => $sports->getKey(),
            'name' => 'Fitness & Training',
            'description' => 'Fitness equipment, training aids, protective equipment, and workout accessories.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('team-sports', [
            'parent_id' => $sports->getKey(),
            'name' => 'Team Sports',
            'description' => 'Football, basketball, volleyball, golf, and other team-sport equipment.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('outdoor-recreation', [
            'parent_id' => $sports->getKey(),
            'name' => 'Outdoor Recreation',
            'description' => 'Camping, hiking, cycling, fishing, archery, and outdoor activity products.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('water-sports', [
            'parent_id' => $sports->getKey(),
            'name' => 'Water Sports',
            'description' => 'Swimming, surfing, pool, beach, and other water-sport products.',
            'sort_order' => 4,
        ]);

        $this->upsertCategory('sports-accessories', [
            'parent_id' => $sports->getKey(),
            'name' => 'Sports Accessories',
            'description' => 'Sports bags, bottles, glasses, protective accessories, and equipment parts.',
            'sort_order' => 5,
        ]);

        // ----------------------------------------------------------------
// Kids, Baby & Toys hierarchy
// ----------------------------------------------------------------

        $kidsBabyToys = $this->upsertCategory('kids-baby-toys', [
            'name' => 'Kids, Baby & Toys',
            'description' => 'Baby products, children\'s toys, educational activities, and play equipment.',
            'sort_order' => 6,
        ]);

        $this->upsertCategory('baby-supplies', [
            'parent_id' => $kidsBabyToys->getKey(),
            'name' => 'Baby Supplies',
            'description' => 'Baby travel gear, nursery products, safety equipment, and baby essentials.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('toys-games', [
            'parent_id' => $kidsBabyToys->getKey(),
            'name' => 'Toys & Games',
            'description' => 'Puzzles, games, figures, blocks, plush toys, and general play products.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('educational-toys', [
            'parent_id' => $kidsBabyToys->getKey(),
            'name' => 'Educational Toys',
            'description' => 'Learning toys, flash cards, writing activities, puzzles, and preschool toys.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('pretend-play-dolls', [
            'parent_id' => $kidsBabyToys->getKey(),
            'name' => 'Pretend Play & Dolls',
            'description' => 'Dolls, dress-up sets, kitchen playsets, costumes, and pretend-play toys.',
            'sort_order' => 4,
        ]);

        $this->upsertCategory('remote-control-toys', [
            'parent_id' => $kidsBabyToys->getKey(),
            'name' => 'Remote Control & Vehicle Toys',
            'description' => 'Remote-control cars, toy vehicles, scooters, and vehicle playsets.',
            'sort_order' => 5,
        ]);

        // ----------------------------------------------------------------
// Office & School Supplies hierarchy
// ----------------------------------------------------------------

        $officeSchool = $this->upsertCategory('office-school-supplies', [
            'name' => 'Office & School Supplies',
            'description' => 'Stationery, educational materials, office organization, and printing supplies.',
            'sort_order' => 7,
        ]);

        $this->upsertCategory('stationery-writing', [
            'parent_id' => $officeSchool->getKey(),
            'name' => 'Stationery & Writing',
            'description' => 'Pens, pencils, markers, notebooks, erasers, rulers, and writing accessories.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('art-drawing-supplies', [
            'parent_id' => $officeSchool->getKey(),
            'name' => 'Art & Drawing Supplies',
            'description' => 'Art sets, colored pencils, drawing tools, painting supplies, and stencils.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('school-educational-supplies', [
            'parent_id' => $officeSchool->getKey(),
            'name' => 'School & Educational Supplies',
            'description' => 'Learning materials, classroom products, presentation supplies, and school essentials.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('office-supplies-furniture', [
            'parent_id' => $officeSchool->getKey(),
            'name' => 'Office Supplies & Furniture',
            'description' => 'Filing products, desk accessories, office storage, chairs, and office furniture.',
            'sort_order' => 4,
        ]);

        $this->upsertCategory('printing-supplies', [
            'parent_id' => $officeSchool->getKey(),
            'name' => 'Printing Supplies',
            'description' => 'Printer ink, toner cartridges, labels, envelopes, and mailing supplies.',
            'sort_order' => 5,
        ]);

        // ----------------------------------------------------------------
// Automotive hierarchy
// ----------------------------------------------------------------

        $automotive = $this->upsertCategory('automotive', [
            'name' => 'Automotive',
            'description' => 'Vehicle electronics, replacement parts, and interior and exterior accessories.',
            'sort_order' => 8,
        ]);

        $this->upsertCategory('car-electronics', [
            'parent_id' => $automotive->getKey(),
            'name' => 'Car Electronics',
            'description' => 'Car electronic systems, phone mounts, intelligence systems, and accessories.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('automotive-interior', [
            'parent_id' => $automotive->getKey(),
            'name' => 'Interior Accessories',
            'description' => 'Accessories and products designed for vehicle interiors.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('automotive-exterior', [
            'parent_id' => $automotive->getKey(),
            'name' => 'Exterior Accessories',
            'description' => 'Accessories and products designed for vehicle exteriors.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('automotive-parts', [
            'parent_id' => $automotive->getKey(),
            'name' => 'Replacement Parts',
            'description' => 'Replacement components, maintenance products, and vehicle parts.',
            'sort_order' => 4,
        ]);

        // ----------------------------------------------------------------
// Pet Supplies hierarchy
// ----------------------------------------------------------------

        $petSupplies = $this->upsertCategory('pet-supplies', [
            'name' => 'Pet Supplies',
            'description' => 'Food, care products, accessories, furniture, and supplies for pets.',
            'sort_order' => 9,
        ]);

        $this->upsertCategory('pet-food-care', [
            'parent_id' => $petSupplies->getKey(),
            'name' => 'Pet Food & Care',
            'description' => 'Pet food, grooming products, healthcare supplies, and feeding accessories.',
            'sort_order' => 1,
        ]);

        $this->upsertCategory('pet-collars-leashes', [
            'parent_id' => $petSupplies->getKey(),
            'name' => 'Collars, Leashes & Harnesses',
            'description' => 'Pet collars, standard leashes, harnesses, and walking sets.',
            'sort_order' => 2,
        ]);

        $this->upsertCategory('pet-beds-carriers-furniture', [
            'parent_id' => $petSupplies->getKey(),
            'name' => 'Beds, Carriers & Furniture',
            'description' => 'Pet beds, blankets, carriers, cat trees, and pet furniture.',
            'sort_order' => 3,
        ]);

        $this->upsertCategory('aquarium-bird-supplies', [
            'parent_id' => $petSupplies->getKey(),
            'name' => 'Aquarium & Bird Supplies',
            'description' => 'Aquarium products, fish supplies, bird houses, bird toys, and bird accessories.',
            'sort_order' => 4,
        ]);

        $this->upsertCategory('pet-accessories', [
            'parent_id' => $petSupplies->getKey(),
            'name' => 'Pet Accessories',
            'description' => 'Pet clothing, shoes, hair accessories, glasses, bowls, and outdoor gear.',
            'sort_order' => 5,
        ]);

        // ----------------------------------------------------------------
        // Brands
        // ----------------------------------------------------------------

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

        if (!file_exists($path)) {
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
