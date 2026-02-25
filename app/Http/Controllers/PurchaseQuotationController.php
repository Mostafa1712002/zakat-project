<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseQuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'warehouse'])
            ->where('is_quotation', true)
            ->orderBy('invoice_date', 'desc');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $quotations = $query->paginate(20);
        return view('purchase-quotations.index', compact('quotations'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('purchase-quotations.create', compact('suppliers', 'warehouses', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $branchId = $request->branch_id
                ?? auth()->user()->branch_id
                ?? Branch::where('is_main', true)->value('id')
                ?? Branch::where('is_active', true)->value('id');

            $purchase = Purchase::create([
                'invoice_number' => Purchase::generateInvoiceNumber(),
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $request->due_date,
                'payment_type' => $request->payment_type ?? 'credit',
                'branch_id' => $branchId,
                'user_id' => auth()->id(),
                'status' => Purchase::STATUS_DRAFT,
                'payment_status' => 'unpaid',
                'discount_type' => $request->discount_type ?? 'fixed',
                'discount_value' => $request->discount_value ?? 0,
                'notes' => $validated['notes'] ?? null,
                'is_quotation' => true,
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
                    'unit_cost' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += $itemSubtotal;
            }

            $discountType = $request->discount_type ?? 'fixed';
            $discountValue = floatval($request->discount_value ?? 0);
            $discountAmount = $discountType === 'percentage' ? ($subtotal * $discountValue / 100) : $discountValue;

            $purchase->subtotal = $subtotal;
            $purchase->discount_amount = $discountAmount;
            $purchase->total_amount = $subtotal - $discountAmount;
            $purchase->remaining_amount = $subtotal - $discountAmount;
            $purchase->paid_amount = 0;
            $purchase->save();

            DB::commit();

            return redirect()->route('purchase-quotations.show', $purchase)
                ->with('success', 'تم إنشاء تسعيرة المشتريات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء التسعيرة: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase_quotation)
    {
        $purchase = $purchase_quotation;
        $purchase->load(['supplier', 'items.product', 'warehouse']);

        $companyName = '';
        $companyLogo = '';
        $companyStamp = '';
        $invoiceContacts = [];
        try {
            $settings = \DB::table('settings')
                ->whereIn('key', ['company_name', 'company_logo', 'company_stamp', 'invoice_contacts'])
                ->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? '';
            $companyLogo = $settings['company_logo'] ?? '';
            $companyStamp = $settings['company_stamp'] ?? '';
            $invoiceContacts = json_decode($settings['invoice_contacts'] ?? '[]', true) ?: [];
        } catch (\Exception $e) {}

        return view('purchase-quotations.show', compact('purchase', 'companyName', 'companyLogo', 'companyStamp', 'invoiceContacts'));
    }

    public function edit(Purchase $purchase_quotation)
    {
        $purchase = $purchase_quotation;
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن تعديل تسعيرة تم تأكيدها');
        }

        $purchase->load('items');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('purchase-quotations.edit', compact('purchase', 'suppliers', 'warehouses', 'products', 'branches'));
    }

    public function update(Request $request, Purchase $purchase_quotation)
    {
        $purchase = $purchase_quotation;
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن تعديل تسعيرة تم تأكيدها');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $request->due_date,
                'payment_type' => $request->payment_type ?? 'credit',
                'discount_type' => $request->discount_type ?? 'fixed',
                'discount_value' => $request->discount_value ?? 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            $purchase->items()->delete();

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
                    'unit_cost' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += $itemSubtotal;
            }

            $discountType = $request->discount_type ?? 'fixed';
            $discountValue = floatval($request->discount_value ?? 0);
            $discountAmount = $discountType === 'percentage' ? ($subtotal * $discountValue / 100) : $discountValue;

            $purchase->subtotal = $subtotal;
            $purchase->discount_amount = $discountAmount;
            $purchase->total_amount = $subtotal - $discountAmount;
            $purchase->remaining_amount = $subtotal - $discountAmount;
            $purchase->paid_amount = 0;
            $purchase->save();

            DB::commit();

            return redirect()->route('purchase-quotations.show', $purchase)
                ->with('success', 'تم تحديث التسعيرة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث التسعيرة: ' . $e->getMessage());
        }
    }

    public function destroy(Purchase $purchase_quotation)
    {
        $purchase = $purchase_quotation;
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن حذف تسعيرة تم تأكيدها');
        }

        $purchase->items()->delete();
        $purchase->delete();

        return redirect()->route('purchase-quotations.index')
            ->with('success', 'تم حذف التسعيرة بنجاح');
    }

    public function confirm(Purchase $purchase)
    {
        if (!$purchase->is_quotation) {
            return back()->with('error', 'هذه ليست تسعيرة');
        }

        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            return back()->with('error', 'هذه التسعيرة تم تأكيدها مسبقاً');
        }

        DB::beginTransaction();

        try {
            $isCash = $purchase->payment_type === 'cash';
            $branchId = $purchase->branch_id;

            // Add to inventory
            $warehouse = Warehouse::find($purchase->warehouse_id);
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                if ($product && $product->track_inventory && $warehouse) {
                    $warehouse->adjustStock(
                        $item->product_id,
                        $item->quantity,
                        'purchase',
                        "استلام تسعيرة شراء {$purchase->invoice_number}",
                        $purchase->invoice_number
                    );
                }
            }

            // Update purchase status
            $purchase->update([
                'is_quotation' => false,
                'status' => Purchase::STATUS_RECEIVED,
                'received_date' => now(),
                'payment_status' => $isCash ? 'paid' : 'unpaid',
                'remaining_amount' => $isCash ? 0 : $purchase->total_amount,
                'paid_amount' => $isCash ? $purchase->total_amount : 0,
            ]);

            // Create expense for cash purchases
            if ($isCash && $purchase->total_amount > 0) {
                $supplier = Supplier::find($purchase->supplier_id);
                $purchaseCategory = ExpenseCategory::where('code', 'PURCHASE')->first()
                    ?? ExpenseCategory::where('name', 'like', '%مشتريات%')->first();

                Expense::create([
                    'expense_number' => Expense::generateExpenseNumber(),
                    'expense_category_id' => $purchaseCategory?->id,
                    'branch_id' => $branchId,
                    'user_id' => auth()->id(),
                    'expense_date' => $purchase->invoice_date,
                    'title' => 'فاتورة مشتريات - ' . $purchase->invoice_number,
                    'description' => 'تأكيد تسعيرة مشتريات من المورد: ' . ($supplier->name ?? 'غير محدد'),
                    'amount' => $purchase->total_amount,
                    'total_amount' => $purchase->total_amount,
                    'payment_method' => 'cash',
                    'vendor_name' => $supplier->name ?? null,
                    'reference_number' => $purchase->invoice_number,
                    'status' => 'paid',
                ]);
            }

            // Update supplier balance for credit purchases
            if (!$isCash) {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $supplier->increment('current_balance', $purchase->total_amount);
                }
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'تم تأكيد التسعيرة وتحويلها لفاتورة مشتريات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تأكيد التسعيرة: ' . $e->getMessage());
        }
    }

    public function pdf(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'warehouse']);

        $companyName = '';
        $companyLogo = '';
        $companyStamp = '';
        $invoiceContacts = [];
        try {
            $settings = \DB::table('settings')
                ->whereIn('key', ['company_name', 'company_logo', 'company_stamp', 'invoice_contacts'])
                ->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? '';
            $companyName = preg_replace('/[\x{1F000}-\x{1FFFF}|\x{2600}-\x{27FF}|\x{FE00}-\x{FEFF}]/u', '', $companyName);
            $companyName = trim($companyName);
            $companyLogo = $settings['company_logo'] ?? '';
            $companyStamp = $settings['company_stamp'] ?? '';
            $invoiceContacts = json_decode($settings['invoice_contacts'] ?? '[]', true) ?: [];
        } catch (\Exception $e) {}

        $pdf = \Barryvdh\Snappy\Facades\SnappyPdf::loadView('pdf.purchase', compact('purchase', 'companyName', 'companyLogo', 'companyStamp', 'invoiceContacts'));

        $pdf->setOption('page-size', 'A4');
        $pdf->setOption('encoding', 'UTF-8');
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->inline('تسعيرة-شراء-' . $purchase->invoice_number . '.pdf');
    }
}
