<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\ImportCatalogCsvAction;
use App\Domain\Catalog\Services\CatalogCsvExporter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportCatalogCsvRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductBulkCatalogController extends Controller
{
    public function __construct(
        private CatalogCsvExporter $exporter,
        private ImportCatalogCsvAction $importCatalogCsvAction,
    ) {}

    public function export(): StreamedResponse
    {
        $this->authorize('viewAny', Product::class);

        return $this->exporter->stream();
    }

    public function template(): StreamedResponse
    {
        $this->authorize('viewAny', Product::class);

        return $this->exporter->template();
    }

    public function import(ImportCatalogCsvRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $result = $this->importCatalogCsvAction->execute($request->file('catalog_csv'), $request->user());

        return redirect()
            ->route('admin.catalog.products.index')
            ->with('catalogImport', $result->toArray())
            ->with(
                $result->failed > 0 ? 'warning' : 'success',
                sprintf(
                    'Catalog import processed %d rows: %d created, %d updated, %d failed.',
                    $result->processed,
                    $result->created,
                    $result->updated,
                    $result->failed,
                ),
            );
    }
}
