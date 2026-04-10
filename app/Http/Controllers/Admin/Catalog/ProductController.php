<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(): Response
    {
        return response('Admin product index placeholder');
    }

    public function create(): Response
    {
        return response('Admin product create placeholder');
    }

    public function store(StoreProductRequest $request): Response
    {
        return response('Admin product store placeholder', Response::HTTP_CREATED);
    }

    public function show(Product $product): Response
    {
        return response("Admin product show placeholder: {$product->getKey()}");
    }

    public function edit(Product $product): Response
    {
        return response("Admin product edit placeholder: {$product->getKey()}");
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
