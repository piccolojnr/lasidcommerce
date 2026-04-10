<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(): InertiaResponse
    {
        return Inertia::render('admin/catalog/products/index');
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/catalog/products/create');
    }

    public function store(StoreProductRequest $request): Response
    {
        return response('Admin product store placeholder', Response::HTTP_CREATED);
    }

    public function show(Product $product): InertiaResponse
    {
        return Inertia::render('admin/catalog/products/show');
    }

    public function edit(Product $product): InertiaResponse
    {
        return Inertia::render('admin/catalog/products/edit');
    }

    public function update(UpdateProductRequest $request, Product $product): Response
    {
        return response("Admin product update placeholder: {$product->getKey()}");
    }

    public function destroy(Product $product): Response
    {
        return response()->noContent();
    }
}
