<?php

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Services\CategorySlugGenerator;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySlugGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private CategorySlugGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CategorySlugGenerator;
    }

    public function test_generates_slug_from_name(): void
    {
        $slug = $this->generator->generate('New Arrivals');

        $this->assertSame('new-arrivals', $slug);
    }

    public function test_appends_suffix_when_slug_exists(): void
    {
        Category::factory()->create(['slug' => 'shoes']);

        $slug = $this->generator->generate('Shoes');

        $this->assertSame('shoes-2', $slug);
    }

    public function test_increments_suffix_until_unique(): void
    {
        Category::factory()->create(['slug' => 'shoes']);
        Category::factory()->create(['slug' => 'shoes-2']);

        $slug = $this->generator->generate('Shoes');

        $this->assertSame('shoes-3', $slug);
    }

    public function test_excludes_given_id_from_uniqueness_check(): void
    {
        $category = Category::factory()->create(['slug' => 'shoes']);

        $slug = $this->generator->generate('Shoes', $category->id);

        $this->assertSame('shoes', $slug);
    }
}
