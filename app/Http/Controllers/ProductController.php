<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function data()
    {
        return response()->json([
            'data' => Product::orderBy('id', 'desc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:products,code',
            'name' => 'required|string|max:255',
            'qty' => 'required|min:0',
            'unit' => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
        ]);

        Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully'
        ]);
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'code')->ignore($product->id)
            ],
            'name' => 'required|string|max:255',
            'qty' => 'required|min:0',
            'unit' => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated'
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted'
        ]);
    }
}
