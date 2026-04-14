<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::query()->where('email', 'catalog.manager@example.com')->first()
            ?? User::query()->where('email', 'admin@example.com')->first();

        $categories = [
            'footwear' => Category::query()->firstWhere('slug', 'footwear'),
            'accessories' => Category::query()->firstWhere('slug', 'accessories'),
        ];

        $brands = [
            'urban-step' => Brand::query()->firstWhere('slug', 'urban-step'),
            'lasid-basics' => Brand::query()->firstWhere('slug', 'lasid-basics'),
        ];

        $products = [
            [
                'sku' => 'LAS-SNK-001',
                'category_slug' => 'footwear',
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
            ],
            [
                'sku' => 'LAS-SND-002',
                'category_slug' => 'footwear',
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
            ],
            [
                'sku' => 'LAS-BAG-003',
                'category_slug' => 'accessories',
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
        }
    }
}
