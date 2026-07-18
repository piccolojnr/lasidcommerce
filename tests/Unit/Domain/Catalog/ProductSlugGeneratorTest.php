<?php

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Services\ProductSlugGenerator;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSlugGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private ProductSlugGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ProductSlugGenerator;
    }

    public function test_generates_slug_from_name(): void
    {
        $slug = $this->generator->generate('Classic Sneaker');

        $this->assertSame('classic-sneaker', $slug);
    }

    public function test_appends_suffix_when_slug_exists(): void
    {
        Product::factory()->create(['slug' => 'running-shoe']);

        $slug = $this->generator->generate('Running Shoe');

        $this->assertSame('running-shoe-2', $slug);
    }

    public function test_increments_suffix_until_unique(): void
    {
        Product::factory()->create(['slug' => 'sport-watch']);
        Product::factory()->create(['slug' => 'sport-watch-2']);

        $slug = $this->generator->generate('Sport Watch');

        $this->assertSame('sport-watch-3', $slug);
    }

    public function test_excludes_given_id_from_uniqueness_check(): void
    {
        $product = Product::factory()->create(['slug' => 'leather-bag']);

        $slug = $this->generator->generate('Leather Bag', $product->id);

        $this->assertSame('leather-bag', $slug);
    }

    public function test_does_not_collide_with_soft_deleted_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'wool-hat']);
        $product->delete(); // soft delete

        // Without withTrashed(), 'wool-hat' appears free — would cause a DB unique violation on create.
        $slug = $this->generator->generate('Wool Hat');

        $this->assertSame('wool-hat-2', $slug);
    }
}
