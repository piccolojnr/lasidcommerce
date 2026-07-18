<?php

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Actions\DeleteProductAction;
use App\Domain\Catalog\Exceptions\CannotDeleteProductException;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteProductActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteProductAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new DeleteProductAction;
    }

    public function test_soft_deletes_product_with_no_order_items(): void
    {
        $product = Product::factory()->create();

        $this->action->execute($product);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_throws_when_product_has_order_items(): void
    {
        $product = Product::factory()->create();

        $orderId = \DB::table('orders')->insertGetId([
            'order_number' => 'TEST-001',
            'email' => 'test@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => $product->base_price,
            'line_total' => $product->base_price,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(CannotDeleteProductException::class);
        $this->expectExceptionMessage('order');

        $this->action->execute($product);
    }

    public function test_does_not_delete_when_order_items_exist(): void
    {
        $product = Product::factory()->create();

        $orderId = \DB::table('orders')->insertGetId([
            'order_number' => 'TEST-002',
            'email' => 'test@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => $product->base_price,
            'line_total' => $product->base_price,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->action->execute($product);
            $this->fail('Expected CannotDeleteProductException was not thrown.');
        } catch (CannotDeleteProductException) {
            // Expected — verify record was not deleted below
        }

        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }
}
