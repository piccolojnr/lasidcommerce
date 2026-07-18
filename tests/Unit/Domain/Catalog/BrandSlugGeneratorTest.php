<?php

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Services\BrandSlugGenerator;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandSlugGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private BrandSlugGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new BrandSlugGenerator;
    }

    public function test_generates_slug_from_name(): void
    {
        $slug = $this->generator->generate('Nike Sportswear');

        $this->assertSame('nike-sportswear', $slug);
    }

    public function test_appends_suffix_when_slug_exists(): void
    {
        Brand::factory()->create(['slug' => 'adidas']);

        $slug = $this->generator->generate('Adidas');

        $this->assertSame('adidas-2', $slug);
    }

    public function test_increments_suffix_until_unique(): void
    {
        Brand::factory()->create(['slug' => 'puma']);
        Brand::factory()->create(['slug' => 'puma-2']);

        $slug = $this->generator->generate('Puma');

        $this->assertSame('puma-3', $slug);
    }

    public function test_excludes_given_id_from_uniqueness_check(): void
    {
        $brand = Brand::factory()->create(['slug' => 'reebok']);

        $slug = $this->generator->generate('Reebok', $brand->id);

        $this->assertSame('reebok', $slug);
    }
}
