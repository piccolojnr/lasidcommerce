<?php

namespace Tests\Feature\Database;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoStoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoStoreSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_database_seeder_does_not_seed_demo_showcase_records(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'customer.ama@example.com']);
        $this->assertDatabaseMissing('products', ['sku' => 'LAS-SNK-001']);
        $this->assertDatabaseMissing('orders', ['order_number' => 'ORD-DEMO-1001']);
    }

    public function test_demo_store_seeder_creates_optional_showcase_data(): void
    {
        $this->seed(DemoStoreSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'catalog.manager@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'customer.ama@example.com']);
        $this->assertDatabaseHas('products', ['sku' => 'LAS-SNK-001', 'status' => 'active']);
        $this->assertDatabaseHas('stock_items', ['product_id' => Product::query()->where('sku', 'LAS-WLT-005')->value('id')]);
        $this->assertDatabaseHas('stock_movements', ['note' => 'Demo opening stock balance.']);
        $this->assertDatabaseHas('orders', ['order_number' => 'ORD-DEMO-1002', 'status' => 'completed']);
        $this->assertDatabaseHas('payments', ['reference' => 'PAY-DEMO-1001', 'status' => 'successful']);
        $this->assertDatabaseHas('shipments', ['tracking_number' => 'TRK-DEMO-1002', 'status' => 'delivered']);
        $this->assertDatabaseHas('order_status_histories', ['to_status' => 'processing']);

        $order = Order::query()->where('order_number', 'ORD-DEMO-1001')->firstOrFail();

        $this->assertCount(2, $order->orderItems);
        $this->assertCount(2, $order->orderAddresses);
        $this->assertCount(1, $order->payments);
        $this->assertCount(1, $order->shipments);

        $this->assertSame(16, Product::query()->where('status', 'active')->count());
        $this->assertSame(16, StockMovement::query()->count());
        $this->assertSame(6, User::query()->whereIn('email', [
            'catalog.manager@example.com',
            'orders.manager@example.com',
            'support.agent@example.com',
            'customer.ama@example.com',
            'customer.kojo@example.com',
            'customer.efua@example.com',
        ])->count());
    }
}
