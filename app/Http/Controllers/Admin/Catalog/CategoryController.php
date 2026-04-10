<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Category::class, 'category');
    }

    public function index(): Response
    {
        return response('Admin category index placeholder');
    }

    public function create(): Response
    {
        return response('Admin category create placeholder');
    }

    public function store(StoreCategoryRequest $request): Response
    {
        return response('Admin category store placeholder', Response::HTTP_CREATED);
    }

    public function show(Category $category): Response
    {
        return response("Admin category show placeholder: {$category->getKey()}");
    }

    public function edit(Category $category): Response
    {
        return response("Admin category edit placeholder: {$category->getKey()}");
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
