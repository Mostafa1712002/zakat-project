<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SalesRep;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'warehouse'])
            ->where('is_quotation', false)
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
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'warehouses', 'branches'));
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
            'items.*.discount_amount' => 'nullable|numeric|min:0|max:100',
            // Advance payment for credit purchases
            'advance_payment' => 'nullable|numeric|min:0',
            'advance_payment_method' => 'nullable|in:cash,bank_transfer,instapay,vodafone_cash,card',
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
                $discountPercent = min(100, max(0, $item['discount_amount'] ?? 0));
                $discountAmount = $itemSubtotal * $discountPercent / 100;
                $itemTotal = $itemSubtotal - $discountAmount;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_price'],
                    'discount_amount' => $discountPercent,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemTotal,
                ]);

                $subtotal += $itemTotal;

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

            $purchase->subtotal = $subtotal;
            $discount = $purchase->discount_type === 'percentage'
                ? $subtotal * ($purchase->discount_value / 100)
                : $purchase->discount_value;
            $purchase->discount_amount = $discount;
            $purchase->total_amount = $subtotal - $discount + ($purchase->shipping_amount ?? 0);
            $purchase->remaining_amount = $isCash ? 0 : $purchase->total_amount;
            $purchase->paid_amount = $isCash ? $purchase->total_amount : 0;
            $purchase->save();

            // التحقق من رصيد الخزنة قبل الدفع
            $amountToPayNow = $isCash ? $purchase->total_amount : floatval($validated['advance_payment'] ?? 0);
            if ($amountToPayNow > 0) {
                $treasuryBalance = $this->getTreasuryBalance();
                if ($amountToPayNow > $treasuryBalance) {
                    throw new \Exception('رصيد الخزنة غير كافي. الرصيد الحالي: ' . number_format($treasuryBalance, 2) . ' ج.م، المطلوب دفعه: ' . number_format($amountToPayNow, 2) . ' ج.م');
                }
            }

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

            // تحديث رصيد المورد عند الشراء الآجل
            if (!$isCash) {
                $supplier = Supplier::find($validated['supplier_id']);
                if ($supplier) {
                    $supplier->increment('current_balance', $purchase->total_amount);
                }

                // إضافة دفعة مقدمة إن وجدت
                $advancePayment = floatval($validated['advance_payment'] ?? 0);
                if ($advancePayment > 0 && $advancePayment <= $purchase->total_amount) {
                    $paymentMethod = $validated['advance_payment_method'] ?? 'cash';

                    Payment::create([
                        'payment_number' => Payment::generatePaymentNumber(Payment::TYPE_PAID),
                        'payable_type' => Supplier::class,
                        'payable_id' => $validated['supplier_id'],
                        'purchase_id' => $purchase->id,
                        'type' => Payment::TYPE_PAID,
                        'amount' => $advancePayment,
                        'method' => $paymentMethod,
                        'payment_date' => $validated['invoice_date'],
                        'branch_id' => $branchId,
                        'user_id' => auth()->id(),
                        'status' => Payment::STATUS_COMPLETED,
                        'notes' => 'دفعة مقدمة - فاتورة شراء رقم ' . $purchase->invoice_number,
                    ]);

                    // تحديث المبلغ المدفوع في الفاتورة
                    $purchase->paid_amount = $advancePayment;
                    $purchase->remaining_amount = $purchase->total_amount - $advancePayment;
                    $purchase->payment_status = $advancePayment >= $purchase->total_amount
                        ? Purchase::PAYMENT_STATUS_PAID
                        : Purchase::PAYMENT_STATUS_PARTIAL;
                    $purchase->save();

                    // خصم الدفعة المقدمة من رصيد المورد
                    $supplier->decrement('current_balance', $advancePayment);

                    // تسجيل مصروف للدفعة المقدمة
                    $advanceCategory = ExpenseCategory::where('code', 'PURCHASE')->first()
                        ?? ExpenseCategory::where('name', 'like', '%مشتريات%')->first();

                    Expense::create([
                        'expense_number' => Expense::generateExpenseNumber(),
                        'expense_category_id' => $advanceCategory?->id,
                        'branch_id' => $branchId,
                        'user_id' => auth()->id(),
                        'expense_date' => $validated['invoice_date'],
                        'title' => 'دفعة مقدمة للمورد: ' . ($supplier->name ?? 'غير محدد'),
                        'description' => 'دفعة مقدمة - فاتورة شراء رقم ' . $purchase->invoice_number,
                        'amount' => $advancePayment,
                        'total_amount' => $advancePayment,
                        'payment_method' => $paymentMethod,
                        'vendor_name' => $supplier->name ?? null,
                        'reference_number' => $purchase->invoice_number,
                        'status' => 'paid',
                    ]);
                }
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

        return view('purchases.show', compact('purchase', 'companyName', 'companyLogo', 'companyStamp', 'invoiceContacts'));
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

        return $pdf->inline($purchase->invoice_number . '.pdf');
    }

    public function edit(Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'لا يمكن تعديل فاتورة ملغاة');
        }

        $purchase->load('items.product');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'warehouses', 'products', 'branches'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'لا يمكن تعديل فاتورة ملغاة');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'status' => 'required|in:draft,ordered,received',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0|max:100',
            'advance_payment' => 'nullable|numeric|min:0',
            'advance_payment_method' => 'nullable|in:cash,bank_transfer,instapay,vodafone_cash,card',
        ]);

        $wasReceived = $purchase->status === 'received';

        DB::beginTransaction();

        try {
            $branchId = $request->branch_id
                ?? auth()->user()->branch_id
                ?? Branch::where('is_main', true)->value('id')
                ?? Branch::where('is_active', true)->value('id');

            $paymentType = $request->payment_type ?? 'credit';
            $isCash = $paymentType === 'cash';
            $newStatus = $validated['status'];

            // 1. If was received → reverse stock for all old items
            if ($wasReceived) {
                $purchase->load('items.product', 'warehouse');
                foreach ($purchase->items as $item) {
                    if ($item->product && $item->product->track_inventory) {
                        $purchase->warehouse->adjustStock(
                            $item->product_id,
                            -$item->quantity,
                            'purchase',
                            "تعديل فاتورة شراء - إرجاع {$purchase->invoice_number}",
                            $purchase->invoice_number
                        );
                    }
                }
            }

            // 2. If was credit → reverse supplier balance for old total
            if ($purchase->payment_type === 'credit') {
                $oldSupplier = Supplier::find($purchase->supplier_id);
                if ($oldSupplier) {
                    $oldSupplier->decrement('current_balance', $purchase->total_amount);
                    // Re-add any payments that were made (they reduced supplier balance)
                    if ($purchase->paid_amount > 0) {
                        $oldSupplier->increment('current_balance', $purchase->paid_amount);
                    }
                }
            }

            // 3. Delete old payments and expenses
            Payment::where('purchase_id', $purchase->id)->delete();
            Expense::where('reference_number', $purchase->invoice_number)->delete();

            // 4. Delete old items
            $purchase->items()->delete();

            // 5. Update purchase header
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $request->due_date,
                'payment_type' => $paymentType,
                'status' => $newStatus,
                'branch_id' => $branchId,
                'discount_type' => $request->discount_type ?? 'fixed',
                'discount_value' => $request->discount_value ?? 0,
                'shipping_amount' => $request->shipping_amount ?? 0,
                'notes' => $request->notes,
                'received_date' => $newStatus === 'received' ? ($purchase->received_date ?? now()) : null,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'payment_status' => 'unpaid',
            ]);

            // 6. Recreate items from validated input
            $subtotal = 0;
            $warehouse = Warehouse::find($validated['warehouse_id']);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $discountPercent = min(100, max(0, $item['discount_amount'] ?? 0));
                $discountAmount = $itemSubtotal * $discountPercent / 100;
                $itemTotal = $itemSubtotal - $discountAmount;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_price'],
                    'discount_amount' => $discountPercent,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemTotal,
                ]);

                $subtotal += $itemTotal;

                // 7. If new status is received → add stock for new items
                if ($newStatus === 'received' && $product->track_inventory) {
                    $warehouse->adjustStock(
                        $item['product_id'],
                        $item['quantity'],
                        'purchase',
                        "استلام فاتورة شراء {$purchase->invoice_number}",
                        $purchase->invoice_number
                    );
                }
            }

            // 8. Recalculate totals
            $purchase->subtotal = $subtotal;
            $discount = $purchase->discount_type === 'percentage'
                ? $subtotal * ($purchase->discount_value / 100)
                : $purchase->discount_value;
            $purchase->discount_amount = $discount;
            $purchase->total_amount = $subtotal - $discount + ($purchase->shipping_amount ?? 0);
            $purchase->remaining_amount = $isCash ? 0 : $purchase->total_amount;
            $purchase->paid_amount = $isCash ? $purchase->total_amount : 0;
            $purchase->payment_status = $isCash ? 'paid' : 'unpaid';
            $purchase->save();

            // 9. Treasury check
            $amountToPayNow = $isCash ? $purchase->total_amount : floatval($validated['advance_payment'] ?? 0);
            if ($amountToPayNow > 0) {
                $treasuryBalance = $this->getTreasuryBalance();
                if ($amountToPayNow > $treasuryBalance) {
                    throw new \Exception('رصيد الخزنة غير كافي. الرصيد الحالي: ' . number_format($treasuryBalance, 2) . ' ج.م، المطلوب دفعه: ' . number_format($amountToPayNow, 2) . ' ج.م');
                }
            }

            // 10. Cash expense
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

            // 11. Credit → update supplier balance + advance payment
            if (!$isCash) {
                $supplier = Supplier::find($validated['supplier_id']);
                if ($supplier) {
                    $supplier->increment('current_balance', $purchase->total_amount);
                }

                $advancePayment = floatval($validated['advance_payment'] ?? 0);
                if ($advancePayment > 0 && $advancePayment <= $purchase->total_amount) {
                    $paymentMethod = $validated['advance_payment_method'] ?? 'cash';

                    Payment::create([
                        'payment_number' => Payment::generatePaymentNumber(Payment::TYPE_PAID),
                        'payable_type' => Supplier::class,
                        'payable_id' => $validated['supplier_id'],
                        'purchase_id' => $purchase->id,
                        'type' => Payment::TYPE_PAID,
                        'amount' => $advancePayment,
                        'method' => $paymentMethod,
                        'payment_date' => $validated['invoice_date'],
                        'branch_id' => $branchId,
                        'user_id' => auth()->id(),
                        'status' => Payment::STATUS_COMPLETED,
                        'notes' => 'دفعة مقدمة - فاتورة شراء رقم ' . $purchase->invoice_number,
                    ]);

                    $purchase->paid_amount = $advancePayment;
                    $purchase->remaining_amount = $purchase->total_amount - $advancePayment;
                    $purchase->payment_status = $advancePayment >= $purchase->total_amount
                        ? Purchase::PAYMENT_STATUS_PAID
                        : Purchase::PAYMENT_STATUS_PARTIAL;
                    $purchase->save();

                    $supplier->decrement('current_balance', $advancePayment);

                    $advanceCategory = ExpenseCategory::where('code', 'PURCHASE')->first()
                        ?? ExpenseCategory::where('name', 'like', '%مشتريات%')->first();

                    Expense::create([
                        'expense_number' => Expense::generateExpenseNumber(),
                        'expense_category_id' => $advanceCategory?->id,
                        'branch_id' => $branchId,
                        'user_id' => auth()->id(),
                        'expense_date' => $validated['invoice_date'],
                        'title' => 'دفعة مقدمة للمورد: ' . ($supplier->name ?? 'غير محدد'),
                        'description' => 'دفعة مقدمة - فاتورة شراء رقم ' . $purchase->invoice_number,
                        'amount' => $advancePayment,
                        'total_amount' => $advancePayment,
                        'payment_method' => $paymentMethod,
                        'vendor_name' => $supplier->name ?? null,
                        'reference_number' => $purchase->invoice_number,
                        'status' => 'paid',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase)->with('success', 'تم تحديث فاتورة المشتريات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الفاتورة: ' . $e->getMessage());
        }
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'لا يمكن حذف فاتورة ملغاة');
        }

        DB::beginTransaction();

        try {
            $purchase->load('items.product', 'warehouse');

            // If status was received → reverse stock
            if ($purchase->status === 'received') {
                foreach ($purchase->items as $item) {
                    if ($item->product && $item->product->track_inventory) {
                        $purchase->warehouse->adjustStock(
                            $item->product_id,
                            -$item->quantity,
                            'purchase',
                            "حذف فاتورة شراء {$purchase->invoice_number}",
                            $purchase->invoice_number
                        );
                    }
                }
            }

            // If credit purchase → decrement supplier balance
            if ($purchase->payment_type === 'credit') {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $supplier->decrement('current_balance', $purchase->total_amount);
                    // Re-add any payments that were made
                    if ($purchase->paid_amount > 0) {
                        $supplier->increment('current_balance', $purchase->paid_amount);
                    }
                }
            }

            // Delete related payments
            Payment::where('purchase_id', $purchase->id)->delete();

            // Delete related expenses by reference_number
            Expense::where('reference_number', $purchase->invoice_number)->delete();

            // Delete items then purchase
            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'تم حذف فاتورة المشتريات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف الفاتورة: ' . $e->getMessage());
        }
    }

    public function confirm(Purchase $purchase)
    {
        if (!in_array($purchase->status, ['draft', 'ordered'])) {
            return back()->with('error', 'لا يمكن تأكيد هذه الفاتورة - الحالة الحالية: ' . $purchase->status);
        }

        DB::beginTransaction();

        try {
            $purchase->load('items.product', 'warehouse');

            // Add stock for tracked products
            foreach ($purchase->items as $item) {
                if ($item->product && $item->product->track_inventory) {
                    $purchase->warehouse->adjustStock(
                        $item->product_id,
                        $item->quantity,
                        'purchase',
                        "تأكيد فاتورة شراء {$purchase->invoice_number}",
                        $purchase->invoice_number
                    );
                }
            }

            // Update status
            $purchase->status = 'received';
            $purchase->received_date = now();
            $purchase->save();

            DB::commit();

            return redirect()->route('purchases.show', $purchase)->with('success', 'تم تأكيد الفاتورة وإضافة المخزون بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تأكيد الفاتورة: ' . $e->getMessage());
        }
    }

    private function getTreasuryBalance(): float
    {
        $openingBalance = Partner::sum('initial_investment')
            + PartnerTransaction::where('type', PartnerTransaction::TYPE_INVESTMENT)->sum('amount')
            - PartnerTransaction::where('type', PartnerTransaction::TYPE_RETURN)->sum('amount');

        $totalCollections = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where(function ($q) {
                $q->where('payable_type', '!=', SalesRep::class)
                  ->orWhereNull('payable_type');
            })
            ->sum('amount');

        $totalCashSales = Sale::where('payment_type', 'cash')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->sum('total_amount');

        $totalRepWithdrawals = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where('payable_type', SalesRep::class)
            ->sum('amount');

        $totalExpenses = Expense::where('status', 'paid')->sum('amount');

        return $openingBalance + ($totalCollections + $totalCashSales + $totalRepWithdrawals) - $totalExpenses;
    }
}
