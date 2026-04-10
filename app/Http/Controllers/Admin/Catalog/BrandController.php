<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Brand::class, 'brand');
    }

    public function index(): InertiaResponse
    {
        return Inertia::render('admin/catalog/brands/index');
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/catalog/brands/create');
    }

    public function store(StoreBrandRequest $request): Response
    {
        return response('Admin brand store placeholder', Response::HTTP_CREATED);
    }

    public function show(Brand $brand): InertiaResponse
    {
        return Inertia::render('admin/catalog/brands/show');
    }

    public function edit(Brand $brand): InertiaResponse
    {
        return Inertia::render('admin/catalog/brands/edit');
    }

    public function update(UpdateBrandRequest $request, Brand $brand): Response
    {
        return response("Admin brand update placeholder: {$brand->getKey()}");
    }

    public function destroy(Brand $brand): Response
    {
        return response()->noContent();
    }
}
