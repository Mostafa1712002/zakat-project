<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'warehouse'])
            ->orderBy('invoice_date', 'desc');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $purchases = $query->paginate(20);
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'warehouses', 'products', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $purchase = Purchase::create([
            'invoice_number' => Purchase::generateInvoiceNumber(),
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $request->due_date,
            'payment_type' => $request->payment_type ?? 'credit',
            'supplier_invoice_number' => $request->supplier_invoice_number,
            'branch_id' => $request->branch_id,
            'user_id' => auth()->id(),
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'discount_type' => $request->discount_type ?? 'fixed',
            'discount_value' => $request->discount_value ?? 0,
            'shipping_amount' => $request->shipping_amount ?? 0,
            'notes' => $request->notes,
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $itemSubtotal = $item['quantity'] * $item['unit_price'];

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $itemSubtotal,
            ]);

            $subtotal += $itemSubtotal;
        }

        $purchase->subtotal = $subtotal;
        $discount = $purchase->discount_type === 'percentage'
            ? $subtotal * ($purchase->discount_value / 100)
            : $purchase->discount_value;
        $purchase->discount_amount = $discount;
        $purchase->total_amount = $subtotal - $discount + ($purchase->shipping_amount ?? 0);
        $purchase->remaining_amount = $purchase->total_amount;
        $purchase->save();

        return redirect()->route('purchases.index')->with('success', 'تم إنشاء فاتورة المشتريات بنجاح');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'warehouse']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'warehouses', 'products', 'branches'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'status' => 'required|in:draft,confirmed,received,cancelled',
        ]);

        $purchase->update($validated + ['notes' => $request->notes]);

        return redirect()->route('purchases.index')->with('success', 'تم تحديث فاتورة المشتريات بنجاح');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->items()->delete();
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'تم حذف فاتورة المشتريات بنجاح');
    }
}
