<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::query()->where('email', 'catalog.manager@example.com')->first()
            ?? User::query()->where('email', 'admin@example.com')->first();

        $categories = [
            'dresses' => Category::query()->firstWhere('slug', 'dresses'),
            'tops' => Category::query()->firstWhere('slug', 'tops'),
            'shirts' => Category::query()->firstWhere('slug', 'shirts'),
            'trousers' => Category::query()->firstWhere('slug', 'trousers'),
            'school-wear' => Category::query()->firstWhere('slug', 'school-wear'),
            'play-wear' => Category::query()->firstWhere('slug', 'play-wear'),
            'footwear' => Category::query()->firstWhere('slug', 'footwear'),
            'sandals' => Category::query()->firstWhere('slug', 'sandals'),
            'sneakers' => Category::query()->firstWhere('slug', 'sneakers'),
            'bags' => Category::query()->firstWhere('slug', 'bags'),
            'work-bags' => Category::query()->firstWhere('slug', 'work-bags'),
            'crossbody-bags' => Category::query()->firstWhere('slug', 'crossbody-bags'),
            'accessories' => Category::query()->firstWhere('slug', 'accessories'),
            'fragrance' => Category::query()->firstWhere('slug', 'fragrance'),
            'skin-care' => Category::query()->firstWhere('slug', 'skin-care'),
            'decor' => Category::query()->firstWhere('slug', 'decor'),
            'bed-bath' => Category::query()->firstWhere('slug', 'bed-bath'),
        ];

        $brands = [
            'urban-step' => Brand::query()->firstWhere('slug', 'urban-step'),
            'lasid-basics' => Brand::query()->firstWhere('slug', 'lasid-basics'),
            'adma-studio' => Brand::query()->firstWhere('slug', 'adma-studio'),
            'northline-home' => Brand::query()->firstWhere('slug', 'northline-home'),
            'gold-coast-care' => Brand::query()->firstWhere('slug', 'gold-coast-care'),
        ];

        $tags = collect([
            ['name' => 'New Season', 'slug' => 'new-season', 'description' => 'Fresh drops and recently launched catalog additions.'],
            ['name' => 'Editor Pick', 'slug' => 'editor-pick', 'description' => 'Manual highlights for strong merchandising placements.'],
            ['name' => 'Occasionwear', 'slug' => 'occasionwear', 'description' => 'Products suited for events, elevated styling, and dressier moments.'],
            ['name' => 'Giftable', 'slug' => 'giftable', 'description' => 'Easy gifting candidates across fashion, beauty, and home.'],
            ['name' => 'Workday', 'slug' => 'workday', 'description' => 'Products that fit office, commute, and weekday routines.'],
            ['name' => 'Weekend Ready', 'slug' => 'weekend-ready', 'description' => 'Relaxed lifestyle picks for weekends and casual shopping.'],
            ['name' => 'Family Essentials', 'slug' => 'family-essentials', 'description' => 'Practical staples for repeat household and family buying.'],
        ])->mapWithKeys(fn (array $definition) => [
            $definition['slug'] => Tag::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_active' => true,
                ],
            ),
        ])->all();

        $collections = collect([
            ['name' => 'New Arrivals', 'slug' => 'new-arrivals', 'description' => 'Recently launched products across the storefront.', 'sort_order' => 10],
            ['name' => 'Weekend Edit', 'slug' => 'weekend-edit', 'description' => 'Relaxed fashion and accessories for off-duty shopping.', 'sort_order' => 20],
            ['name' => 'Workday Rotation', 'slug' => 'workday-rotation', 'description' => 'Sharper wardrobe and commute-ready accessories.', 'sort_order' => 30],
            ['name' => 'Home Refresh', 'slug' => 'home-refresh', 'description' => 'Curated home and self-care products for quick store upgrades.', 'sort_order' => 40],
            ['name' => 'Top Picks', 'slug' => 'top-picks', 'description' => 'Manual highlights spanning the strongest seeded products.', 'sort_order' => 50],
        ])->mapWithKeys(fn (array $definition) => [
            $definition['slug'] => Collection::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_active' => true,
                    'sort_order' => $definition['sort_order'],
                ],
            ),
        ])->all();

        $products = [
            [
                'sku' => 'LAS-SNK-001',
                'category_slug' => 'sneakers',
                'brand_slug' => 'urban-step',
                'name' => 'Urban Sprint Sneaker',
                'slug' => 'urban-sprint-sneaker',
                'short_description' => 'Everyday lightweight sneaker for fast city movement.',
                'description' => 'A breathable low-top sneaker built for daily wear, with cushioned comfort and a clean profile for storefront demos.',
                'base_price' => 32500,
                'compare_at_price' => 39000,
                'cost_price' => 18000,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => true,
                'published_at' => now()->subDays(14),
                'quantity_on_hand' => 30,
                'quantity_reserved' => 4,
                'reorder_level' => 8,
                'tag_slugs' => ['new-season', 'editor-pick', 'weekend-ready'],
                'collection_slugs' => ['new-arrivals', 'weekend-edit', 'top-picks'],
            ],
            [
                'sku' => 'LAS-SND-002',
                'category_slug' => 'sandals',
                'brand_slug' => 'urban-step',
                'name' => 'Coastline Slide Sandal',
                'slug' => 'coastline-slide-sandal',
                'short_description' => 'Easy slip-on sandal for casual warm-weather wear.',
                'description' => 'A minimal slide sandal used to demonstrate secondary footwear categories and lower price points.',
                'base_price' => 18500,
                'compare_at_price' => 22000,
                'cost_price' => 9000,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(10),
                'quantity_on_hand' => 12,
                'quantity_reserved' => 1,
                'reorder_level' => 5,
                'tag_slugs' => ['weekend-ready'],
                'collection_slugs' => ['weekend-edit'],
            ],
            [
                'sku' => 'LAS-BAG-003',
                'category_slug' => 'work-bags',
                'brand_slug' => 'lasid-basics',
                'name' => 'Metro Carry Tote',
                'slug' => 'metro-carry-tote',
                'short_description' => 'Structured tote bag with all-day storage.',
                'description' => 'A versatile carry tote used for featured merchandising and bundled cart demos.',
                'base_price' => 27500,
                'compare_at_price' => 32000,
                'cost_price' => 15000,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => true,
                'published_at' => now()->subDays(7),
                'quantity_on_hand' => 18,
                'quantity_reserved' => 2,
                'reorder_level' => 6,
                'tag_slugs' => ['workday', 'giftable', 'editor-pick'],
                'collection_slugs' => ['workday-rotation', 'top-picks'],
            ],
            [
                'sku' => 'LAS-CAP-004',
                'category_slug' => 'accessories',
                'brand_slug' => 'lasid-basics',
                'name' => 'Signature Street Cap',
                'slug' => 'signature-street-cap',
                'short_description' => 'Classic cap with adjustable fit and embroidered front.',
                'description' => 'Useful for admin catalog demos because it shows a simpler accessory record with healthy stock.',
                'base_price' => 9500,
                'compare_at_price' => 12000,
                'cost_price' => 4500,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(5),
                'quantity_on_hand' => 40,
                'quantity_reserved' => 3,
                'reorder_level' => 10,
                'tag_slugs' => ['weekend-ready'],
                'collection_slugs' => ['weekend-edit'],
            ],
            [
                'sku' => 'LAS-WLT-005',
                'category_slug' => 'accessories',
                'brand_slug' => 'lasid-basics',
                'name' => 'Contour Leather Wallet',
                'slug' => 'contour-leather-wallet',
                'short_description' => 'Compact everyday wallet with multiple card slots.',
                'description' => 'Designed to show a premium accessory with low available inventory for dashboard demos.',
                'base_price' => 16500,
                'compare_at_price' => 21000,
                'cost_price' => 9200,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => true,
                'published_at' => now()->subDays(3),
                'quantity_on_hand' => 6,
                'quantity_reserved' => 2,
                'reorder_level' => 5,
                'tag_slugs' => ['giftable', 'editor-pick'],
                'collection_slugs' => ['top-picks'],
            ],
            [
                'sku' => 'ADM-DRS-006',
                'category_slug' => 'dresses',
                'brand_slug' => 'adma-studio',
                'name' => 'ADMA Midi Occasion Dress',
                'slug' => 'adma-midi-occasion-dress',
                'short_description' => 'Soft-structure midi dress for events and evening styling.',
                'description' => 'An elevated women\'s dress with polished silhouette details to make the seeded catalog feel less generic.',
                'base_price' => 45500,
                'compare_at_price' => 52000,
                'cost_price' => 26000,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => true,
                'published_at' => now()->subDays(12),
                'quantity_on_hand' => 14,
                'quantity_reserved' => 2,
                'reorder_level' => 4,
                'tag_slugs' => ['occasionwear', 'editor-pick', 'new-season'],
                'collection_slugs' => ['new-arrivals', 'top-picks'],
            ],
            [
                'sku' => 'ADM-TOP-007',
                'category_slug' => 'tops',
                'brand_slug' => 'adma-studio',
                'name' => 'ADMA Linen Wrap Top',
                'slug' => 'adma-linen-wrap-top',
                'short_description' => 'Breathable wrap top for smart-casual everyday wear.',
                'description' => 'A lightweight wrap silhouette to seed women\'s separates and layered styling options.',
                'base_price' => 22800,
                'compare_at_price' => 27500,
                'cost_price' => 12000,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(9),
                'quantity_on_hand' => 22,
                'quantity_reserved' => 1,
                'reorder_level' => 6,
                'tag_slugs' => ['new-season'],
                'collection_slugs' => ['new-arrivals'],
            ],
            [
                'sku' => 'ADB-SHT-008',
                'category_slug' => 'shirts',
                'brand_slug' => 'adma-studio',
                'name' => 'ADMA Oxford Work Shirt',
                'slug' => 'adma-oxford-work-shirt',
                'short_description' => 'Crisp work shirt cut for office-ready rotation.',
                'description' => 'A menswear staple for catalog demos that need a cleaner top-level category split than generic fashion.',
                'base_price' => 26500,
                'compare_at_price' => 31000,
                'cost_price' => 14500,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(8),
                'quantity_on_hand' => 19,
                'quantity_reserved' => 2,
                'reorder_level' => 5,
                'tag_slugs' => ['workday'],
                'collection_slugs' => ['workday-rotation'],
            ],
            [
                'sku' => 'ADB-TRS-009',
                'category_slug' => 'trousers',
                'brand_slug' => 'adma-studio',
                'name' => 'ADMA Tailored Commuter Trouser',
                'slug' => 'adma-tailored-commuter-trouser',
                'short_description' => 'Smart-casual trouser built for weekday wear.',
                'description' => 'A dependable menswear bottom for order demos and higher basket mixes.',
                'base_price' => 29800,
                'compare_at_price' => 35000,
                'cost_price' => 17000,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => true,
                'published_at' => now()->subDays(6),
                'quantity_on_hand' => 16,
                'quantity_reserved' => 3,
                'reorder_level' => 5,
                'tag_slugs' => ['workday', 'editor-pick'],
                'collection_slugs' => ['workday-rotation', 'top-picks'],
            ],
            [
                'sku' => 'KID-UNI-010',
                'category_slug' => 'school-wear',
                'brand_slug' => 'lasid-basics',
                'name' => 'Junior School Set',
                'slug' => 'junior-school-set',
                'short_description' => 'Uniform-ready shirt and shorts combo for school runs.',
                'description' => 'A practical kids category seed for merchandising school and family shopping journeys.',
                'base_price' => 17800,
                'compare_at_price' => 21000,
                'cost_price' => 9200,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(11),
                'quantity_on_hand' => 24,
                'quantity_reserved' => 4,
                'reorder_level' => 8,
                'tag_slugs' => ['family-essentials'],
                'collection_slugs' => ['top-picks'],
            ],
            [
                'sku' => 'KID-PLY-011',
                'category_slug' => 'play-wear',
                'brand_slug' => 'lasid-basics',
                'name' => 'Weekend Play Set',
                'slug' => 'weekend-play-set',
                'short_description' => 'Soft kids play set for movement and everyday comfort.',
                'description' => 'A high-turn kids item that helps the seeded catalog feel broader and more realistic.',
                'base_price' => 14200,
                'compare_at_price' => 16800,
                'cost_price' => 7600,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(4),
                'quantity_on_hand' => 28,
                'quantity_reserved' => 2,
                'reorder_level' => 10,
                'tag_slugs' => ['family-essentials', 'weekend-ready'],
                'collection_slugs' => ['weekend-edit'],
            ],
            [
                'sku' => 'LAS-XBD-012',
                'category_slug' => 'crossbody-bags',
                'brand_slug' => 'lasid-basics',
                'name' => 'Commute Crossbody Mini',
                'slug' => 'commute-crossbody-mini',
                'short_description' => 'Compact crossbody bag for errands and light travel.',
                'description' => 'A small-format bag product that rounds out the bags hierarchy and supports mobile-first storefront demos.',
                'base_price' => 21500,
                'compare_at_price' => 25500,
                'cost_price' => 11800,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(5),
                'quantity_on_hand' => 20,
                'quantity_reserved' => 2,
                'reorder_level' => 6,
                'tag_slugs' => ['giftable', 'weekend-ready'],
                'collection_slugs' => ['weekend-edit'],
            ],
            [
                'sku' => 'GCC-FRG-013',
                'category_slug' => 'fragrance',
                'brand_slug' => 'gold-coast-care',
                'name' => 'Sunrise Body Mist',
                'slug' => 'sunrise-body-mist',
                'short_description' => 'Fresh daily body mist with citrus opening notes.',
                'description' => 'A beauty-category seed to balance the catalog beyond fashion and accessories.',
                'base_price' => 13200,
                'compare_at_price' => 15800,
                'cost_price' => 6800,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => true,
                'published_at' => now()->subDays(13),
                'quantity_on_hand' => 25,
                'quantity_reserved' => 1,
                'reorder_level' => 7,
                'tag_slugs' => ['giftable', 'editor-pick'],
                'collection_slugs' => ['home-refresh', 'top-picks'],
            ],
            [
                'sku' => 'GCC-SKN-014',
                'category_slug' => 'skin-care',
                'brand_slug' => 'gold-coast-care',
                'name' => 'Cocoa Glow Body Butter',
                'slug' => 'cocoa-glow-body-butter',
                'short_description' => 'Rich body butter for evening skin hydration.',
                'description' => 'A skin-care staple that makes the beauty branch usable in storefront filtering demos.',
                'base_price' => 14800,
                'compare_at_price' => 17600,
                'cost_price' => 7900,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(10),
                'quantity_on_hand' => 21,
                'quantity_reserved' => 2,
                'reorder_level' => 6,
                'tag_slugs' => ['giftable'],
                'collection_slugs' => ['home-refresh'],
            ],
            [
                'sku' => 'NHL-DEC-015',
                'category_slug' => 'decor',
                'brand_slug' => 'northline-home',
                'name' => 'Woven Accent Basket',
                'slug' => 'woven-accent-basket',
                'short_description' => 'Decorative woven basket for home organization and styling.',
                'description' => 'A practical home decor item to make non-fashion catalog branches worth demoing.',
                'base_price' => 18900,
                'compare_at_price' => 22500,
                'cost_price' => 10200,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => false,
                'published_at' => now()->subDays(7),
                'quantity_on_hand' => 17,
                'quantity_reserved' => 1,
                'reorder_level' => 5,
                'tag_slugs' => ['giftable'],
                'collection_slugs' => ['home-refresh'],
            ],
            [
                'sku' => 'NHL-BTH-016',
                'category_slug' => 'bed-bath',
                'brand_slug' => 'northline-home',
                'name' => 'Cloud Cotton Bath Set',
                'slug' => 'cloud-cotton-bath-set',
                'short_description' => 'Bath towel set for premium daily home use.',
                'description' => 'A bed-and-bath seed with strong basket-building behavior for demo orders.',
                'base_price' => 24200,
                'compare_at_price' => 29000,
                'cost_price' => 13600,
                'track_inventory' => true,
                'allow_backorders' => false,
                'is_featured' => true,
                'published_at' => now()->subDays(2),
                'quantity_on_hand' => 13,
                'quantity_reserved' => 2,
                'reorder_level' => 4,
                'tag_slugs' => ['giftable', 'editor-pick', 'new-season'],
                'collection_slugs' => ['new-arrivals', 'home-refresh', 'top-picks'],
            ],
        ];

        foreach ($products as $definition) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $definition['sku']],
                [
                    'category_id' => $categories[$definition['category_slug']]?->getKey(),
                    'brand_id' => $brands[$definition['brand_slug']]?->getKey(),
                    'name' => $definition['name'],
                    'slug' => $definition['slug'],
                    'short_description' => $definition['short_description'],
                    'description' => $definition['description'],
                    'status' => 'active',
                    'product_type' => 'physical',
                    'base_price' => $definition['base_price'],
                    'compare_at_price' => $definition['compare_at_price'],
                    'cost_price' => $definition['cost_price'],
                    'track_inventory' => $definition['track_inventory'],
                    'allow_backorders' => $definition['allow_backorders'],
                    'is_featured' => $definition['is_featured'],
                    'published_at' => $definition['published_at'],
                ]
            );

            $stockItem = StockItem::query()->updateOrCreate(
                ['product_id' => $product->getKey(), 'product_variant_id' => null],
                [
                    'quantity_on_hand' => $definition['quantity_on_hand'],
                    'quantity_reserved' => $definition['quantity_reserved'],
                    'reorder_level' => $definition['reorder_level'],
                ]
            );

            StockMovement::query()->updateOrCreate(
                [
                    'stock_item_id' => $stockItem->getKey(),
                    'type' => 'restock',
                    'reference_type' => Product::class,
                    'reference_id' => $product->getKey(),
                ],
                [
                    'quantity' => $definition['quantity_on_hand'],
                    'note' => 'Demo opening stock balance.',
                    'created_by' => $actor?->getKey(),
                ]
            );

            $product->tags()->sync(
                collect($definition['tag_slugs'] ?? [])
                    ->map(fn (string $slug) => $tags[$slug]?->getKey())
                    ->filter()
                    ->values()
                    ->all(),
            );

            $collectionSyncData = [];
            foreach (collect($definition['collection_slugs'] ?? [])->values() as $index => $slug) {
                $collectionId = $collections[$slug]?->getKey();

                if ($collectionId === null) {
                    continue;
                }

                $collectionSyncData[$collectionId] = [
                    'sort_order' => ($index + 1) * 10,
                ];
            }

            $product->collections()->sync($collectionSyncData);

            $this->syncProductImages($product, $definition);

        }

    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function syncProductImages(Product $product, array $definition): void
    {
        $product->clearMediaCollection('images');

        foreach (['Front View', 'Detail View'] as $index => $label) {
            $svg = $this->buildProductImageSvg($definition, $label, $index);

            $product
                ->addMediaFromString($svg)
                ->usingFileName(sprintf('%s-%s.svg', Str::slug($product->sku), Str::slug($label)))
                ->usingName(sprintf('%s %s', $product->name, $label))
                ->withCustomProperties([
                    'seeded' => true,
                    'position' => $index + 1,
                    'label' => $label,
                ])
                ->toMediaCollection('images');
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function buildProductImageSvg(array $definition, string $label, int $index): string
    {
        [$primary, $secondary, $accent] = $this->paletteForCategory((string) $definition['category_slug']);

        $name = $this->escapeSvgText((string) $definition['name']);
        $category = strtoupper(str_replace('-', ' ', (string) $definition['category_slug']));
        $price = number_format(((int) $definition['base_price']) / 100, 2);
        $safeLabel = $this->escapeSvgText($label);

        $circleX = $index === 0 ? '630' : '160';
        $circleY = $index === 0 ? '120' : '520';
        $shapeOpacity = $index === 0 ? '0.18' : '0.24';

        return <<<SVG
<svg width="1200" height="1200" viewBox="0 0 1200 1200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg" x1="120" y1="80" x2="1060" y2="1080" gradientUnits="userSpaceOnUse">
      <stop stop-color="{$primary}"/>
      <stop offset="1" stop-color="{$secondary}"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="1200" rx="0" fill="url(#bg)"/>
  <circle cx="{$circleX}" cy="{$circleY}" r="220" fill="#FFFFFF" fill-opacity="{$shapeOpacity}"/>
  <circle cx="940" cy="940" r="180" fill="{$accent}" fill-opacity="0.14"/>
  <rect x="92" y="92" width="1016" height="1016" rx="56" stroke="#FFFFFF" stroke-opacity="0.18" stroke-width="4"/>
  <text x="110" y="160" fill="#FFFFFF" fill-opacity="0.72" font-size="34" font-family="Arial, Helvetica, sans-serif" letter-spacing="6">{$category}</text>
  <text x="110" y="272" fill="#FFFFFF" font-size="74" font-weight="700" font-family="Arial, Helvetica, sans-serif">{$name}</text>
  <text x="110" y="350" fill="#FFFFFF" fill-opacity="0.82" font-size="38" font-family="Arial, Helvetica, sans-serif">{$safeLabel}</text>
  <rect x="110" y="826" width="300" height="88" rx="44" fill="#FFFFFF" fill-opacity="0.16"/>
  <text x="150" y="883" fill="#FFFFFF" font-size="40" font-weight="700" font-family="Arial, Helvetica, sans-serif">GH₵ {$price}</text>
  <text x="110" y="1004" fill="#FFFFFF" fill-opacity="0.68" font-size="28" font-family="Arial, Helvetica, sans-serif">Demo catalog seed image</text>
  <text x="110" y="1048" fill="#FFFFFF" fill-opacity="0.52" font-size="24" font-family="Arial, Helvetica, sans-serif">SKU {$definition['sku']}</text>
</svg>
SVG;
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function paletteForCategory(string $categorySlug): array
    {
        return match ($categorySlug) {
            'sneakers', 'sandals', 'footwear' => ['#102542', '#1F5A85', '#7FDBFF'],
            'work-bags', 'crossbody-bags', 'bags', 'accessories' => ['#3B1F2B', '#7D3C5B', '#F6B8C8'],
            'dresses', 'tops', 'shirts', 'trousers' => ['#243B2E', '#496A55', '#D9F0E4'],
            'school-wear', 'play-wear' => ['#6A2C70', '#B83B5E', '#F08A5D'],
            'fragrance', 'skin-care' => ['#7A4E1D', '#D08C3F', '#FFF0C9'],
            'decor', 'bed-bath' => ['#264653', '#2A9D8F', '#E9F5DB'],
            default => ['#1F2937', '#4B5563', '#E5E7EB'],
        };
    }

    private function escapeSvgText(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
