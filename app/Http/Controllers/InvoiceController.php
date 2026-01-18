<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\SalesRep;
use App\Models\Branch;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'warehouse', 'salesRep'])
            ->orderBy('invoice_date', 'desc');

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $invoices = $query->paginate(20);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $salesReps = SalesRep::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('invoices.create', compact('customers', 'warehouses', 'products', 'salesReps', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_type' => 'required|in:cash,credit',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $branchId = $request->branch_id
            ?? auth()->user()?->branch_id
            ?? Branch::where('is_main', true)->value('id')
            ?? Branch::where('is_active', true)->value('id');

        $sale = Sale::create([
            'invoice_number' => Sale::generateInvoiceNumber(),
            'customer_id' => $validated['customer_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'payment_type' => $validated['payment_type'],
            'sales_rep_id' => $request->sales_rep_id,
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'status' => 'confirmed',
            'payment_status' => $validated['payment_type'] === 'cash' ? 'paid' : 'unpaid',
            'discount_type' => $request->discount_type ?? 'fixed',
            'discount_value' => $request->discount_value ?? 0,
            'shipping_amount' => $request->shipping_amount ?? 0,
            'notes' => $request->notes,
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $itemSubtotal = $item['quantity'] * $item['unit_price'] - ($item['discount_amount'] ?? 0);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'cost_price' => $product->cost_price,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'subtotal' => $itemSubtotal,
            ]);

            $subtotal += $itemSubtotal;
        }

        $sale->subtotal = $subtotal;
        $sale->calculateTotals();
        $sale->save();

        return redirect()->route('invoices.index')->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    public function show(Sale $invoice)
    {
        $invoice->load(['customer', 'items.product', 'warehouse', 'salesRep']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Sale $invoice)
    {
        $invoice->load('items');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $salesReps = SalesRep::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'customers', 'warehouses', 'products', 'salesReps', 'branches'));
    }

    public function update(Request $request, Sale $invoice)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'status' => 'required|in:draft,confirmed,delivered,cancelled',
        ]);

        $invoice->update($validated + [
            'notes' => $request->notes,
        ]);

        return redirect()->route('invoices.index')->with('success', 'تم تحديث الفاتورة بنجاح');
    }

    public function destroy(Sale $invoice)
    {
        // Only allow deleting draft invoices
        if ($invoice->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن حذف فاتورة تم تأكيدها');
        }

        // Check if invoice has payments
        if ($invoice->paid_amount > 0) {
            return back()->with('error', 'لا يمكن حذف فاتورة تم دفع جزء منها');
        }

        $invoice->items()->delete();
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة بنجاح');
    }

    public function print(Sale $invoice)
    {
        $invoice->load(['customer', 'items.product', 'warehouse', 'salesRep', 'branch']);
        return view('invoices.print', compact('invoice'));
    }
}
