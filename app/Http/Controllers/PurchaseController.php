<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'status' => 'required|in:draft,ordered,received',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Get branch_id with fallback
            $branchId = $request->branch_id
                ?? auth()->user()->branch_id
                ?? Branch::where('is_main', true)->value('id')
                ?? Branch::where('is_active', true)->value('id');

            $paymentType = $request->payment_type ?? 'credit';
            $isCash = $paymentType === 'cash';
            $status = $validated['status'];
            $isReceived = $status === 'received';

            $purchase = Purchase::create([
                'invoice_number' => Purchase::generateInvoiceNumber(),
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $request->due_date,
                'received_date' => $isReceived ? now() : null,
                'payment_type' => $paymentType,
                'supplier_invoice_number' => $request->supplier_invoice_number,
                'branch_id' => $branchId,
                'user_id' => auth()->id(),
                'status' => $status,
                'payment_status' => $isCash ? 'paid' : 'unpaid',
                'discount_type' => $request->discount_type ?? 'fixed',
                'discount_value' => $request->discount_value ?? 0,
                'shipping_amount' => $request->shipping_amount ?? 0,
                'notes' => $request->notes,
            ]);

            $subtotal = 0;
            $warehouse = Warehouse::find($validated['warehouse_id']);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $itemSubtotal = $item['quantity'] * $item['unit_price'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += $itemSubtotal;

                // إضافة للمخزن إذا كانت الحالة "مستلم"
                if ($isReceived && $product->track_inventory) {
                    $warehouse->adjustStock(
                        $item['product_id'],
                        $item['quantity'],
                        'purchase',
                        "استلام فاتورة شراء {$purchase->invoice_number}",
                        $purchase->invoice_number
                    );
                }
            }

            // ربط الأصناف بالمورد تلقائياً
            $supplier = Supplier::find($validated['supplier_id']);
            $productIds = collect($validated['items'])->pluck('product_id')->unique()->toArray();
            $supplier->products()->syncWithoutDetaching($productIds);

            $purchase->subtotal = $subtotal;
            $discount = $purchase->discount_type === 'percentage'
                ? $subtotal * ($purchase->discount_value / 100)
                : $purchase->discount_value;
            $purchase->discount_amount = $discount;
            $purchase->total_amount = $subtotal - $discount + ($purchase->shipping_amount ?? 0);
            $purchase->remaining_amount = $isCash ? 0 : $purchase->total_amount;
            $purchase->paid_amount = $isCash ? $purchase->total_amount : 0;
            $purchase->save();

            // إنشاء مصروف تلقائي للمشتريات النقدية
            if ($isCash && $purchase->total_amount > 0) {
                $supplier = Supplier::find($validated['supplier_id']);
                $purchaseCategory = ExpenseCategory::where('code', 'PURCHASE')->first()
                    ?? ExpenseCategory::where('name', 'like', '%مشتريات%')->first();

                Expense::create([
                    'expense_number' => Expense::generateExpenseNumber(),
                    'expense_category_id' => $purchaseCategory?->id,
                    'branch_id' => $branchId,
                    'user_id' => auth()->id(),
                    'expense_date' => $validated['invoice_date'],
                    'title' => 'فاتورة مشتريات - ' . $purchase->invoice_number,
                    'description' => 'مشتريات نقدية من المورد: ' . ($supplier->name ?? 'غير محدد'),
                    'amount' => $purchase->total_amount,
                    'total_amount' => $purchase->total_amount,
                    'payment_method' => 'cash',
                    'vendor_name' => $supplier->name ?? null,
                    'reference_number' => $purchase->invoice_number,
                    'status' => 'paid',
                    'notes' => $request->notes,
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'تم إنشاء فاتورة المشتريات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء الفاتورة: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'warehouse']);

        $companyName = '';
        $companyLogo = '';
        $companyStamp = '';
        try {
            $settings = \DB::table('settings')
                ->whereIn('key', ['company_name', 'company_logo', 'company_stamp'])
                ->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? '';
            $companyLogo = $settings['company_logo'] ?? '';
            $companyStamp = $settings['company_stamp'] ?? '';
        } catch (\Exception $e) {}

        return view('purchases.show', compact('purchase', 'companyName', 'companyLogo', 'companyStamp'));
    }

    public function pdf(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'warehouse']);

        $companyName = '';
        $companyLogo = '';
        $companyStamp = '';
        try {
            $settings = \DB::table('settings')
                ->whereIn('key', ['company_name', 'company_logo', 'company_stamp'])
                ->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? '';
            $companyLogo = $settings['company_logo'] ?? '';
            $companyStamp = $settings['company_stamp'] ?? '';
        } catch (\Exception $e) {}

        $pdf = \Barryvdh\Snappy\Facades\SnappyPdf::loadView('pdf.purchase', compact('purchase', 'companyName', 'companyLogo', 'companyStamp'));

        $pdf->setOption('page-size', 'A4');
        $pdf->setOption('encoding', 'UTF-8');
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);

        return $pdf->inline($purchase->invoice_number . '.pdf');
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
            'status' => 'required|in:draft,ordered,received,cancelled',
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $purchase->status;
            $newStatus = $validated['status'];

            // Update purchase
            $purchase->update($validated + ['notes' => $request->notes]);

            // إذا تم تغيير الحالة من أي حالة إلى "مستلم" - إضافة للمخزن
            if ($oldStatus !== 'received' && $newStatus === 'received') {
                $purchase->load('items.product', 'warehouse');

                foreach ($purchase->items as $item) {
                    if ($item->product && $item->product->track_inventory) {
                        // إضافة الكمية للمخزن
                        $purchase->warehouse->adjustStock(
                            $item->product_id,
                            $item->quantity,
                            'purchase',
                            "استلام فاتورة شراء {$purchase->invoice_number}",
                            $purchase->invoice_number
                        );
                    }
                }

                // تسجيل تاريخ الاستلام
                $purchase->received_date = now();
                $purchase->save();
            }

            // إذا تم إلغاء فاتورة كانت مستلمة - خصم من المخزن
            if ($oldStatus === 'received' && $newStatus === 'cancelled') {
                $purchase->load('items.product', 'warehouse');

                foreach ($purchase->items as $item) {
                    if ($item->product && $item->product->track_inventory) {
                        // خصم الكمية من المخزن (إلغاء الاستلام)
                        $purchase->warehouse->adjustStock(
                            $item->product_id,
                            -$item->quantity,
                            'purchase',
                            "إلغاء فاتورة شراء {$purchase->invoice_number}",
                            $purchase->invoice_number
                        );
                    }
                }
            }

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'تم تحديث فاتورة المشتريات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->items()->delete();
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'تم حذف فاتورة المشتريات بنجاح');
    }
}
