<?php

namespace App\Domain\Catalog\Services;

use App\Models\Product;

class ProductBadgeService
{
    public function __construct(
        private CatalogSettingsService $settings,
    ) {}

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public function resolve(Product $product): array
    {
        $badges = [];

        if ($this->isNewArrival($product)) {
            $badges[] = [
                'key' => 'new_arrival',
                'label' => 'New arrival',
            ];
        }

        if ($product->isOnSale()) {
            $badges[] = [
                'key' => 'on_sale',
                'label' => 'On sale',
            ];
        }

        return $badges;
    }

    private function isNewArrival(Product $product): bool
    {
        if ($product->published_at === null || $product->published_at->isFuture()) {
            return false;
        }

        return $product->published_at->greaterThanOrEqualTo(
            now()->subDays($this->settings->newArrivalWindowDays()),
        );
    }
}
