<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Product;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage products', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage products');
    }

    public function test_product_edit_includes_options_and_variants(): void
    {
        $product = Product::factory()->create();
        $size = ProductOptionType::factory()->create([
            'product_id' => $product->id,
            'name' => 'Size',
        ]);
        $medium = ProductOptionValue::factory()->create([
            'option_type_id' => $size->id,
            'value' => 'Medium',
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Medium',
            'sku' => 'SKU-M',
        ]);
        $variant->optionValues()->attach($medium);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.catalog.products.edit', $product));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/edit')
            ->where('product.option_types.0.name', 'Size')
            ->where('product.option_types.0.values.0.value', 'Medium')
            ->where('product.variants.0.name', 'Medium')
            ->where('product.variants.0.option_values.0.value', 'Medium')
        );
    }

    public function test_admin_can_create_option_type_and_value(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.catalog.products.edit', $product))
            ->post(route('admin.catalog.products.option-types.store', $product), [
                'name' => 'Color',
            ])
            ->assertRedirect(route('admin.catalog.products.edit', $product));

        $optionType = ProductOptionType::query()->where('product_id', $product->id)->firstOrFail();

        $this->actingAs($this->admin)
            ->from(route('admin.catalog.products.edit', $product))
            ->post(route('admin.catalog.option-types.values.store', $optionType), [
                'value' => 'Black',
            ])
            ->assertRedirect(route('admin.catalog.products.edit', $product));

        $this->assertDatabaseHas('product_option_types', [
            'product_id' => $product->id,
            'name' => 'Color',
        ]);
        $this->assertDatabaseHas('product_option_values', [
            'option_type_id' => $optionType->id,
            'value' => 'Black',
        ]);
    }

    public function test_admin_can_create_variant_with_option_values(): void
    {
        $product = Product::factory()->create(['track_inventory' => true]);
        $size = ProductOptionType::factory()->create(['product_id' => $product->id]);
        $medium = ProductOptionValue::factory()->create(['option_type_id' => $size->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.catalog.products.edit', $product))
            ->post(route('admin.catalog.products.variants.store', $product), [
                'name' => 'Medium',
                'sku' => 'SKU-MED',
                'price' => 2500,
                'option_value_ids' => [$medium->id],
            ])
            ->assertRedirect(route('admin.catalog.products.edit', $product));

        $variant = ProductVariant::query()->where('sku', 'SKU-MED')->firstOrFail();

        $this->assertDatabaseHas('product_variant_option_values', [
            'product_variant_id' => $variant->id,
            'option_value_id' => $medium->id,
        ]);
        $this->assertDatabaseHas('stock_items', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_variant_option_values_must_belong_to_same_product(): void
    {
        $product = Product::factory()->create();
        $otherProduct = Product::factory()->create();
        $otherOption = ProductOptionType::factory()->create(['product_id' => $otherProduct->id]);
        $otherValue = ProductOptionValue::factory()->create(['option_type_id' => $otherOption->id]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.catalog.products.edit', $product))
            ->post(route('admin.catalog.products.variants.store', $product), [
                'name' => 'Invalid',
                'sku' => 'SKU-INVALID',
                'option_value_ids' => [$otherValue->id],
            ]);

        $response->assertSessionHasErrors('option_value_ids');
        $this->assertDatabaseMissing('product_variants', ['sku' => 'SKU-INVALID']);
    }

    public function test_variant_cannot_select_two_values_for_same_option(): void
    {
        $product = Product::factory()->create();
        $size = ProductOptionType::factory()->create(['product_id' => $product->id]);
        $small = ProductOptionValue::factory()->create(['option_type_id' => $size->id]);
        $medium = ProductOptionValue::factory()->create(['option_type_id' => $size->id]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.catalog.products.edit', $product))
            ->post(route('admin.catalog.products.variants.store', $product), [
                'name' => 'Invalid',
                'sku' => 'SKU-TWO-SIZES',
                'option_value_ids' => [$small->id, $medium->id],
            ]);

        $response->assertSessionHasErrors('option_value_ids');
        $this->assertDatabaseMissing('product_variants', ['sku' => 'SKU-TWO-SIZES']);
    }

    public function test_admin_can_update_and_delete_variant(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'SKU-OLD',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.catalog.products.edit', $product))
            ->patch(route('admin.catalog.variants.update', $variant), [
                'name' => 'Updated',
                'sku' => 'SKU-NEW',
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.catalog.products.edit', $product));

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'name' => 'Updated',
            'sku' => 'SKU-NEW',
            'is_active' => false,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.catalog.products.edit', $product))
            ->delete(route('admin.catalog.variants.destroy', $variant))
            ->assertRedirect(route('admin.catalog.products.edit', $product));

        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    public function test_admin_can_clear_variant_option_values(): void
    {
        $product = Product::factory()->create();
        $size = ProductOptionType::factory()->create(['product_id' => $product->id]);
        $medium = ProductOptionValue::factory()->create(['option_type_id' => $size->id]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'SKU-CLEAR',
        ]);
        $variant->optionValues()->attach($medium);

        $this->actingAs($this->admin)
            ->from(route('admin.catalog.products.edit', $product))
            ->patch(route('admin.catalog.variants.update', $variant), [
                'name' => $variant->name,
                'sku' => $variant->sku,
                'option_value_ids' => [''],
            ])
            ->assertRedirect(route('admin.catalog.products.edit', $product));

        $this->assertDatabaseMissing('product_variant_option_values', [
            'product_variant_id' => $variant->id,
            'option_value_id' => $medium->id,
        ]);
    }
}
