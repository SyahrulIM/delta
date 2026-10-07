<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        return view('suppliers.index');
    }

    public function data()
    {
        return response()->json([
            'data' => Supplier::orderBy('id', 'desc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:50',
        ]);

        Supplier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully'
        ]);
    }

    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:50',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated'
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted'
        ]);
    }
}
