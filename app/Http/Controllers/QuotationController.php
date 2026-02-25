<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\SalesRep;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Payment;
use App\Models\Grade;
use App\Models\InventoryLevel;
use App\Models\SalesRepInventory;
use App\Models\SalesRepStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'warehouse', 'user'])
            ->where('is_quotation', true)
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $quotations = $query->paginate(20);
        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::active()->get();
        $warehouses = Warehouse::active()->get();
        $branches = Branch::where('is_active', true)->get();
        $salesReps = SalesRep::where('is_active', true)->get();
        $products = Product::active()->with('unit')->get();
        $grades = feature_enabled('grade_system') ? Grade::active()->ordered()->get() : collect();

        return view('quotations.create', compact('customers', 'products', 'warehouses', 'branches', 'salesReps', 'grades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sales_rep_id' => 'nullable|exists:sales_reps,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'payment_type' => 'required|in:cash,credit',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.grade_id' => 'nullable|exists:grades,id',
        ]);

        DB::beginTransaction();

        try {
            $branchId = $validated['branch_id']
                ?? auth()->user()->branch_id
                ?? Branch::where('is_main', true)->value('id')
                ?? Branch::where('is_active', true)->value('id');

            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $validated['customer_id'],
                'branch_id' => $branchId,
                'warehouse_id' => $validated['warehouse_id'],
                'sales_rep_id' => $validated['sales_rep_id'] ?? null,
                'user_id' => auth()->id(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'payment_type' => $validated['payment_type'],
                'status' => Sale::STATUS_DRAFT,
                'payment_status' => Sale::PAYMENT_STATUS_UNPAID,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
                'discount_value' => $validated['discount_value'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'is_quotation' => true,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $subtotal = $item['quantity'] * $item['unit_price'];
                $discountPercent = min(100, max(0, $item['discount_amount'] ?? 0));
                $discount = $subtotal * $discountPercent / 100;
                $taxAmount = $product->is_taxable ? ($subtotal - $discount) * ($product->tax_rate / 100) : 0;
                $total = $subtotal - $discount + $taxAmount;

                $saleItemData = [
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $product->cost_price,
                    'discount_amount' => $discountPercent,
                    'tax_rate' => $product->is_taxable ? $product->tax_rate : 0,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $subtotal,
                    'total' => $total,
                ];

                if (feature_enabled('tile_area_tracking') && $product->area_per_unit) {
                    $saleItemData['total_area'] = $item['quantity'] * $product->area_per_unit;
                }

                if (feature_enabled('grade_system') && !empty($item['grade_id'])) {
                    $saleItemData['grade_id'] = $item['grade_id'];
                }

                SaleItem::create($saleItemData);
            }

            $sale->calculateTotals();
            $sale->save();

            DB::commit();

            return redirect()->route('quotations.show', $sale)
                ->with('success', 'تم إنشاء التسعيرة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء التسعيرة: ' . $e->getMessage());
        }
    }

    public function show(Sale $quotation)
    {
        $sale = $quotation;
        $itemRelations = ['items.product'];
        if (feature_enabled('grade_system')) {
            $itemRelations[] = 'items.grade';
        }
        $sale->load(array_merge(['customer', 'branch', 'warehouse', 'salesRep', 'user'], $itemRelations));

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

        return view('quotations.show', compact('sale', 'companyName', 'companyLogo', 'companyStamp', 'invoiceContacts'));
    }

    public function edit(Sale $quotation)
    {
        $sale = $quotation;
        if ($sale->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن تعديل تسعيرة تم تأكيدها');
        }

        $editItemRelations = ['items.product'];
        if (feature_enabled('grade_system')) {
            $editItemRelations[] = 'items.grade';
        }
        $sale->load($editItemRelations);

        $customers = Customer::active()->get();
        $warehouses = Warehouse::active()->get();
        $branches = Branch::where('is_active', true)->get();
        $salesReps = SalesRep::where('is_active', true)->get();
        $products = Product::active()->with('unit')->get();
        $grades = feature_enabled('grade_system') ? Grade::active()->ordered()->get() : collect();

        return view('quotations.edit', compact('sale', 'customers', 'products', 'warehouses', 'branches', 'salesReps', 'grades'));
    }

    public function update(Request $request, Sale $quotation)
    {
        $sale = $quotation;
        if ($sale->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن تعديل تسعيرة تم تأكيدها');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sales_rep_id' => 'nullable|exists:sales_reps,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'payment_type' => 'required|in:cash,credit',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.grade_id' => 'nullable|exists:grades,id',
        ]);

        DB::beginTransaction();

        try {
            $branchId = $validated['branch_id']
                ?? auth()->user()->branch_id
                ?? Branch::where('is_main', true)->value('id')
                ?? Branch::where('is_active', true)->value('id');

            $sale->update([
                'customer_id' => $validated['customer_id'],
                'branch_id' => $branchId,
                'warehouse_id' => $validated['warehouse_id'],
                'sales_rep_id' => $validated['sales_rep_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'payment_type' => $validated['payment_type'],
                'discount_type' => $validated['discount_type'] ?? 'fixed',
                'discount_value' => $validated['discount_value'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            $sale->items()->delete();

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $subtotal = $item['quantity'] * $item['unit_price'];
                $discountPercent = min(100, max(0, $item['discount_amount'] ?? 0));
                $discount = $subtotal * $discountPercent / 100;
                $taxAmount = $product->is_taxable ? ($subtotal - $discount) * ($product->tax_rate / 100) : 0;
                $total = $subtotal - $discount + $taxAmount;

                $saleItemData = [
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $product->cost_price,
                    'discount_amount' => $discountPercent,
                    'tax_rate' => $product->is_taxable ? $product->tax_rate : 0,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $subtotal,
                    'total' => $total,
                ];

                if (feature_enabled('tile_area_tracking') && $product->area_per_unit) {
                    $saleItemData['total_area'] = $item['quantity'] * $product->area_per_unit;
                }

                if (feature_enabled('grade_system') && !empty($item['grade_id'])) {
                    $saleItemData['grade_id'] = $item['grade_id'];
                }

                SaleItem::create($saleItemData);
            }

            $sale->calculateTotals();
            $sale->save();

            DB::commit();

            return redirect()->route('quotations.show', $sale)
                ->with('success', 'تم تحديث التسعيرة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث التسعيرة: ' . $e->getMessage());
        }
    }

    public function destroy(Sale $quotation)
    {
        $sale = $quotation;
        if ($sale->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن حذف تسعيرة تم تأكيدها');
        }

        DB::beginTransaction();
        try {
            $sale->items()->delete();
            $sale->delete();
            DB::commit();

            return redirect()->route('quotations.index')
                ->with('success', 'تم حذف التسعيرة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف التسعيرة: ' . $e->getMessage());
        }
    }

    public function confirm(Sale $sale)
    {
        if (!$sale->is_quotation) {
            return back()->with('error', 'هذه ليست تسعيرة');
        }

        if ($sale->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'هذه التسعيرة تم تأكيدها مسبقاً');
        }

        // Check stock availability
        $stockErrors = [];
        foreach ($sale->items as $item) {
            $product = Product::find($item->product_id);
            if ($product && $product->track_inventory) {
                $warehouse = Warehouse::find($sale->warehouse_id);
                $availableStock = $warehouse ? $warehouse->getAvailableStock($product->id) : 0;
                if ($item->quantity > $availableStock) {
                    $stockErrors[] = "الصنف \"{$product->name}\" - الكمية المطلوبة ({$item->quantity}) أكبر من المتاح ({$availableStock})";
                }
            }
        }

        if (!empty($stockErrors)) {
            return back()->with('error', 'الكميات غير متوفرة: ' . implode(' | ', $stockErrors));
        }

        DB::beginTransaction();

        try {
            // Deduct inventory
            if ($sale->sales_rep_id) {
                foreach ($sale->items as $item) {
                    SalesRepStockMovement::record(
                        $sale->sales_rep_id,
                        $item->product_id,
                        SalesRepStockMovement::TYPE_SALE,
                        $item->quantity,
                        $sale->warehouse_id,
                        $sale->id,
                        'بيع - تسعيرة ' . $sale->invoice_number
                    );
                }
            } else {
                foreach ($sale->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product && $product->track_inventory) {
                        StockMovement::recordMovement([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $sale->warehouse_id,
                            'type' => StockMovement::TYPE_OUT,
                            'quantity' => $item->quantity,
                            'unit_cost' => $item->cost_price,
                            'reference_type' => Sale::class,
                            'reference_id' => $sale->id,
                            'reference_number' => $sale->invoice_number,
                            'user_id' => auth()->id(),
                            'reason' => 'بيع - تأكيد تسعيرة',
                        ]);
                    }
                }
            }

            // Convert to confirmed sale
            $sale->update([
                'is_quotation' => false,
                'status' => Sale::STATUS_CONFIRMED,
            ]);

            // Auto payment for cash sales
            if (feature_enabled('auto_cash_payment') && $sale->payment_type === 'cash' && $sale->total_amount > 0) {
                $branchId = $sale->branch_id;
                $salesRepId = $sale->sales_rep_id;

                $payment = Payment::create([
                    'payment_number' => Payment::generatePaymentNumber(Payment::TYPE_RECEIVED),
                    'payable_type' => Sale::class,
                    'payable_id' => $sale->id,
                    'sale_id' => $sale->id,
                    'type' => Payment::TYPE_RECEIVED,
                    'amount' => $sale->total_amount,
                    'method' => Payment::METHOD_CASH,
                    'payment_date' => $sale->invoice_date,
                    'branch_id' => $branchId,
                    'user_id' => auth()->id(),
                    'sales_rep_id' => $salesRepId,
                    'status' => Payment::STATUS_COMPLETED,
                    'notes' => 'تحصيل نقدي - تأكيد تسعيرة ' . $sale->invoice_number,
                ]);

                $sale->paid_amount = $sale->total_amount;
                $sale->remaining_amount = 0;
                $sale->payment_status = Sale::PAYMENT_STATUS_PAID;
                $sale->save();

                if ($salesRepId) {
                    $salesRep = SalesRep::find($salesRepId);
                    if ($salesRep) {
                        $salesRep->recordCollection($sale->total_amount, 'تحصيل نقدي - تسعيرة ' . $sale->invoice_number, $payment->id);
                    }
                }
            }

            // Recalculate customer balance
            $customer = Customer::find($sale->customer_id);
            if ($customer) {
                $customer->recalculateBalance();
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'تم تأكيد التسعيرة وتحويلها لفاتورة مبيعات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تأكيد التسعيرة: ' . $e->getMessage());
        }
    }

    public function pdf(Sale $sale)
    {
        $pdfItemRelations = ['items.product'];
        if (feature_enabled('grade_system')) {
            $pdfItemRelations[] = 'items.grade';
        }
        $sale->load(array_merge(['customer', 'branch', 'warehouse', 'salesRep', 'user'], $pdfItemRelations));

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

        $pdf = \Barryvdh\Snappy\Facades\SnappyPdf::loadView('pdf.sale', compact('sale', 'companyName', 'companyLogo', 'companyStamp', 'invoiceContacts'));

        $pdf->setOption('page-size', 'A4');
        $pdf->setOption('encoding', 'UTF-8');
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->inline('تسعيرة-' . $sale->invoice_number . '.pdf');
    }
}
