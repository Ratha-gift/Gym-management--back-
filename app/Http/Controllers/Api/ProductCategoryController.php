<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return ProductCategory::withCount('products')->orderBy('category_name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        return response()->json(ProductCategory::create($data), 201);
    }

    public function show(ProductCategory $productCategory)
    {
        return $productCategory->load('products');
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $data = $request->validate([
            'category_name' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $productCategory->update($data);

        return $productCategory;
    }

    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete();

        return response()->json(null, 204);
    }
}
