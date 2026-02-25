<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('suppliedProducts')
            ->withSum(['purchases as total_remaining' => function ($q) {
                $q->whereIn('payment_status', ['unpaid', 'partial'])
                  ->where('status', '!=', 'cancelled')
                  ->where('remaining_amount', '>', 0);
            }], 'remaining_amount')
            ->orderBy('name')
            ->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('suppliers.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:suppliers,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'payment_terms_days' => 'nullable|integer|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Auto-generate supplier code if not provided
        if (empty($validated['code'])) {
            do {
                $validated['code'] = 'SUPP-' . date('Ymd') . '-' . rand(100, 999);
            } while (Supplier::where('code', $validated['code'])->exists());
        }

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'تم إضافة المورد بنجاح');
    }

    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $categories = Category::orderBy('name')->get();
        return view('suppliers.edit', compact('supplier', 'categories'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:suppliers,code,' . $supplier->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'payment_terms_days' => 'nullable|integer|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'تم تحديث المورد بنجاح');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'تم حذف المورد بنجاح');
    }

    /**
     * AJAX: Get products associated with a supplier.
     */
    public function products(Supplier $supplier)
    {
        $products = Product::where('is_active', true)
            ->where('supplier_id', $supplier->id)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'cost_price' => $product->cost_price,
                ];
            });

        return response()->json($products);
    }
}
