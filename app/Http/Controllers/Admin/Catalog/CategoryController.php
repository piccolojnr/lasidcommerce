<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Category::class, 'category');
    }

    public function index(): InertiaResponse
    {
        return Inertia::render('admin/catalog/categories/index');
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/catalog/categories/create');
    }

    public function store(StoreCategoryRequest $request): Response
    {
        return response('Admin category store placeholder', Response::HTTP_CREATED);
    }

    public function show(Category $category): InertiaResponse
    {
        return Inertia::render('admin/catalog/categories/show');
    }

    public function edit(Category $category): InertiaResponse
    {
        return Inertia::render('admin/catalog/categories/edit');
    }

    public function update(UpdateCategoryRequest $request, Category $category): Response
    {
        return response("Admin category update placeholder: {$category->getKey()}");
    }

    public function destroy(Category $category): Response
    {
        return response()->noContent();
    }
}
