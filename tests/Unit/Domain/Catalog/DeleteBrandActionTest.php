<?php

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Actions\DeleteBrandAction;
use App\Domain\Catalog\Exceptions\CannotDeleteBrandException;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteBrandActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteBrandAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new DeleteBrandAction;
    }

    public function test_soft_deletes_brand_with_no_products(): void
    {
        $brand = Brand::factory()->create();

        $this->action->execute($brand);

        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_throws_when_brand_has_products(): void
    {
        $brand = Brand::factory()->create();
        // No ProductFactory exists yet — insert directly.
        \DB::table('products')->insert([
            'brand_id' => $brand->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(CannotDeleteBrandException::class);
        $this->expectExceptionMessage('products');

        $this->action->execute($brand);
    }

    public function test_does_not_delete_when_products_exist(): void
    {
        $brand = Brand::factory()->create();
        \DB::table('products')->insert([
            'brand_id' => $brand->id,
            'name' => 'Test Product',
            'slug' => 'test-product-2',
            'sku' => 'TEST-002',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->action->execute($brand);
            $this->fail('Expected CannotDeleteBrandException was not thrown.');
        } catch (CannotDeleteBrandException) {
            // Expected — verify record was not deleted below
        }

        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'deleted_at' => null]);
    }
}
