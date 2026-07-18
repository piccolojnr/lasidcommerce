<?php

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Actions\DeleteCategoryAction;
use App\Domain\Catalog\Exceptions\CannotDeleteCategoryException;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new DeleteCategoryAction;
    }

    public function test_soft_deletes_category_with_no_children_or_products(): void
    {
        $category = Category::factory()->create();

        $this->action->execute($category);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_throws_when_category_has_children(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $this->expectException(CannotDeleteCategoryException::class);
        $this->expectExceptionMessage('subcategories');

        $this->action->execute($parent);
    }

    public function test_does_not_delete_when_children_exist(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        try {
            $this->action->execute($parent);
            $this->fail('Expected CannotDeleteCategoryException was not thrown.');
        } catch (CannotDeleteCategoryException) {
            // Expected — verify the record was not deleted below
        }

        $this->assertDatabaseHas('categories', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_throws_when_category_has_products(): void
    {
        $category = Category::factory()->create();
        \DB::table('products')->insert([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(CannotDeleteCategoryException::class);
        $this->expectExceptionMessage('products');

        $this->action->execute($category);
    }

    public function test_does_not_delete_when_products_exist(): void
    {
        $category = Category::factory()->create();
        \DB::table('products')->insert([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product-2',
            'sku' => 'TEST-002',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->action->execute($category);
            $this->fail('Expected CannotDeleteCategoryException was not thrown.');
        } catch (CannotDeleteCategoryException) {
            // Expected — verify the record was not deleted below
        }

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }
}
