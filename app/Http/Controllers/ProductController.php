<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'unit', 'suppliers'])
            ->withSum('inventoryLevels as stock_quantity', 'quantity')
            ->latest()
            ->paginate(15);

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $units = Unit::active()->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $existingProducts = Product::select('id', 'name', 'sku')->orderBy('name')->get();

        return view('products.create', compact('categories', 'units', 'suppliers', 'existingProducts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_selling_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'track_inventory' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_taxable'] = $request->boolean('is_taxable');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['track_inventory'] = $request->boolean('track_inventory', true);

        // Auto-generate SKU if not provided
        if (empty($validated['sku'])) {
            do {
                $validated['sku'] = 'PRD-' . Str::upper(Str::random(8));
            } while (Product::where('sku', $validated['sku'])->exists());
        }

        // Auto-generate Barcode if not provided
        if (empty($validated['barcode'])) {
            do {
                $validated['barcode'] = date('Ymd') . rand(1000, 9999);
            } while (Product::where('barcode', $validated['barcode'])->exists());
        }

        $product = Product::create($validated);

        if ($request->has('supplier_ids')) {
            $product->suppliers()->sync($request->input('supplier_ids', []));
        }

        return redirect()->route('products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'unit', 'inventoryLevels.warehouse']);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load('suppliers');
        $categories = Category::active()->ordered()->get();
        $units = Unit::active()->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $existingProducts = Product::select('id', 'name', 'sku')
            ->where('id', '!=', $product->id)
            ->orderBy('name')
            ->get();

        return view('products.edit', compact('product', 'categories', 'units', 'suppliers', 'existingProducts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_selling_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'track_inventory' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_taxable'] = $request->boolean('is_taxable');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['track_inventory'] = $request->boolean('track_inventory');

        if (empty($validated['sku'])) {
            $validated['sku'] = $product->sku;
        }

        if (empty($validated['sku'])) {
            do {
                $validated['sku'] = 'PRD-' . Str::upper(Str::random(8));
            } while (Product::where('sku', $validated['sku'])->exists());
        }

        $product->update($validated);

        $product->suppliers()->sync($request->input('supplier_ids', []));

        return redirect()->route('products.index')
            ->with('success', 'تم تحديث المنتج بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Check if product has sales or purchases
        if ($product->inventoryLevels()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'لا يمكن حذف المنتج لأنه يحتوي على مخزون');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'تم حذف المنتج بنجاح');
    }
}
