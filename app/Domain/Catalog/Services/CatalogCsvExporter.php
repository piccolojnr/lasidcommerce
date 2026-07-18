<?php

namespace App\Domain\Catalog\Services;

use App\Models\Product;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogCsvExporter
{
    public function stream(): StreamedResponse
    {
        $filename = 'catalog-products-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, CatalogCsvSchema::HEADERS);

            Product::query()
                ->with(['category', 'brand', 'tags', 'collections', 'stockItems'])
                ->orderBy('sku')
                ->chunk(250, function ($products) use ($handle): void {
                    foreach ($products as $product) {
                        /** @var Product $product */
                        $stockItem = $product->stockItems->firstWhere('product_variant_id', null);

                        fputcsv($handle, [
                            $product->sku,
                            $product->name,
                            $product->slug,
                            $product->status,
                            $product->product_type,
                            $product->category?->slug ?? $product->category?->name,
                            $product->brand?->slug ?? $product->brand?->name,
                            $product->base_price,
                            $product->compare_at_price,
                            $product->cost_price,
                            $product->track_inventory ? '1' : '0',
                            $product->allow_backorders ? '1' : '0',
                            $product->is_featured ? '1' : '0',
                            $product->published_at?->toDateTimeString(),
                            $product->short_description,
                            $product->description,
                            $product->tags->pluck('slug')->implode('|'),
                            $product->collections->pluck('slug')->implode('|'),
                            $stockItem?->quantity_on_hand,
                            $stockItem?->reorder_level,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, CatalogCsvSchema::HEADERS);
            fputcsv($handle, [
                'SKU-001',
                'Example Product',
                '',
                'draft',
                'physical',
                'new-arrivals',
                'example-brand',
                '25000',
                '',
                '12000',
                '1',
                '0',
                '0',
                '',
                'Short merchandising copy.',
                'Full product description.',
                'summer|featured',
                'homepage|new-arrivals',
                '20',
                '5',
            ]);

            fclose($handle);
        }, 'catalog-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
