<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\Response;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Brand::class, 'brand');
    }

    public function index(): Response
    {
        return response('Admin brand index placeholder');
    }

    public function create(): Response
    {
        return response('Admin brand create placeholder');
    }

    public function store(StoreBrandRequest $request): Response
    {
        return response('Admin brand store placeholder', Response::HTTP_CREATED);
    }

    public function show(Brand $brand): Response
    {
        return response("Admin brand show placeholder: {$brand->getKey()}");
    }

    public function edit(Brand $brand): Response
    {
        return response("Admin brand edit placeholder: {$brand->getKey()}");
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
