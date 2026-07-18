<?php

namespace App\Domain\Catalog\Services;

class CatalogCsvSchema
{
    public const HEADERS = [
        'sku',
        'name',
        'slug',
        'status',
        'product_type',
        'category',
        'brand',
        'base_price_cents',
        'compare_at_price_cents',
        'cost_price_cents',
        'track_inventory',
        'allow_backorders',
        'is_featured',
        'published_at',
        'short_description',
        'description',
        'tags',
        'collections',
        'quantity_on_hand',
        'reorder_level',
    ];
}
