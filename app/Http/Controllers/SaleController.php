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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryLevel;
use App\Models\SalesRepInventory;
use App\Models\SalesRepStockMovement;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['customer', 'branch', 'warehouse', 'user', 'salesRep'])
            ->forSalesRep()
            ->latest()
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    /**
     * Get available stock for a product in a warehouse.
     */
    public function getStock(Request $request)
    {
        $productId = $request->get('product_id');
        $warehouseId = $request->get('warehouse_id');

        if (!$productId) {
            return response()->json(['stock' => 0, 'available' => 0]);
        }

        $user = auth()->user();

        // إذا كان المستخدم مندوب - أرجع مخزون المندوب (لا يحتاج warehouse_id)
        if ($user->isSalesRep() && $user->salesRep) {
            $repInventory = SalesRepInventory::where('sales_rep_id', $user->salesRep->id)
                ->where('product_id', $productId)
                ->first();

            if (!$repInventory) {
                return response()->json(['stock' => 0, 'available' => 0, 'source' => 'rep']);
            }

            return response()->json([
                'stock' => (float) $repInventory->quantity,
                'available' => (float) $repInventory->available_quantity,
                'reserved' => (float) $repInventory->reserved_quantity,
                'source' => 'rep',
            ]);
        }

        // للأدمن - أرجع مخزون المخزن (يحتاج warehouse_id)
        if (!$warehouseId) {
            // إذا لم يتم تحديد مخزن، حاول جلب المخزون من كل المخازن
            $totalStock = InventoryLevel::where('product_id', $productId)->sum('quantity');
            $totalReserved = InventoryLevel::where('product_id', $productId)->sum('reserved_quantity');
            return response()->json([
                'stock' => (float) $totalStock,
                'available' => (float) max(0, $totalStock - $totalReserved),
                'reserved' => (float) $totalReserved,
                'source' => 'all_warehouses',
            ]);
        }

        $level = InventoryLevel::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$level) {
            return response()->json(['stock' => 0, 'available' => 0]);
        }

        return response()->json([
            'stock' => (float) $level->quantity,
            'available' => (float) $level->available_quantity,
            'reserved' => (float) $level->reserved_quantity,
            'source' => 'warehouse',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $branches = Branch::where('is_active', true)->get();

        // إذا كان المستخدم مندوب مبيعات
        if ($user->isSalesRep() && $user->salesRep) {
            $salesRep = $user->salesRep;
            // المندوب يرى فقط عملائه والمخازن المرتبطة به
            $customers = Customer::forSalesRep($salesRep->id)->active()->get();
            $warehouses = $salesRep->warehouses()->where('is_active', true)->get();
            $salesReps = collect(); // لا يرى قائمة المندوبين
            $currentSalesRep = $salesRep;

            // المندوب يرى فقط الأصناف المخصصة له والتي لها كمية > 0
            $repInventory = $salesRep->inventory()->where('quantity', '>', 0)->with('product.unit')->get();
            $products = $repInventory->map(function ($item) {
                $product = $item->product;
                $product->rep_stock = $item->quantity;
                return $product;
            })->filter(fn($p) => $p && $p->is_active);
            $useSalesRepInventory = true;
        } else {
            $customers = Customer::active()->get();
            $warehouses = Warehouse::active()->get();
            $salesReps = SalesRep::where('is_active', true)->get();
            $currentSalesRep = null;

            // الأدمن يرى فقط الأصناف الموجودة في المخزن (كمية > 0)
            $productIdsWithStock = InventoryLevel::where('quantity', '>', 0)->pluck('product_id')->unique();
            $products = Product::active()->with('unit')->whereIn('id', $productIdsWithStock)->get();
            $useSalesRepInventory = false;
        }

        return view('sales.create', compact('customers', 'products', 'warehouses', 'branches', 'salesReps', 'currentSalesRep', 'useSalesRepInventory'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
            'shipping_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'purchase_order_number' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            // Items validation
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            // Advance payment for credit sales
            'advance_payment' => 'nullable|numeric|min:0',
            'advance_payment_method' => 'nullable|in:cash,bank_transfer,instapay,vodafone_cash,card',
        ]);

        // التحقق من توفر الكميات
        $stockErrors = [];
        $user = auth()->user();
        $isSalesRep = $user->isSalesRep() && $user->salesRep;

        foreach ($validated['items'] as $index => $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->track_inventory) {
                if ($isSalesRep) {
                    // المندوب: التحقق من مخزون المندوب
                    $repInventory = SalesRepInventory::where('sales_rep_id', $user->salesRep->id)
                        ->where('product_id', $item['product_id'])
                        ->first();
                    $availableStock = $repInventory ? $repInventory->available_quantity : 0;
                    $source = 'مخزنك';
                } else {
                    // الأدمن: التحقق من مخزون المخزن
                    $warehouse = Warehouse::find($validated['warehouse_id']);
                    $availableStock = $warehouse ? $warehouse->getAvailableStock($product->id) : 0;
                    $source = 'المخزن';
                }

                if ($item['quantity'] > $availableStock) {
                    $stockErrors[] = "الصنف \"{$product->name}\" - الكمية المطلوبة ({$item['quantity']}) أكبر من المتاح في {$source} ({$availableStock})";
                }
            }
        }

        if (!empty($stockErrors)) {
            return back()
                ->withInput()
                ->with('error', 'الكميات غير متوفرة: ' . implode(' | ', $stockErrors));
        }

        DB::beginTransaction();

        try {
            // تعيين المندوب تلقائياً إذا كان المستخدم مندوب
            $salesRepId = $validated['sales_rep_id'] ?? null;
            if ($user->isSalesRep() && $user->salesRep) {
                $salesRepId = $user->salesRep->id;
            }

            // Get branch_id with fallback
            $branchId = $validated['branch_id']
                ?? auth()->user()->branch_id
                ?? \App\Models\Branch::where('is_main', true)->value('id')
                ?? \App\Models\Branch::where('is_active', true)->value('id');

            // Create the sale
            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $validated['customer_id'],
                'branch_id' => $branchId,
                'warehouse_id' => $validated['warehouse_id'],
                'sales_rep_id' => $salesRepId,
                'user_id' => auth()->id(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'payment_type' => $validated['payment_type'],
                'status' => Sale::STATUS_DRAFT,
                'payment_status' => Sale::PAYMENT_STATUS_UNPAID,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
                'discount_value' => $validated['discount_value'] ?? 0,
                'shipping_amount' => $validated['shipping_amount'] ?? 0,
                'payment_method' => $validated['payment_method'] ?? null,
                'purchase_order_number' => $validated['purchase_order_number'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
            ]);

            // Create sale items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                $subtotal = $item['quantity'] * $item['unit_price'];
                $discount = $item['discount_amount'] ?? 0;
                $taxAmount = $product->is_taxable ? ($subtotal - $discount) * ($product->tax_rate / 100) : 0;
                $total = $subtotal - $discount + $taxAmount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $product->cost_price,
                    'discount_amount' => $discount,
                    'tax_rate' => $product->is_taxable ? $product->tax_rate : 0,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $subtotal,
                    'total' => $total,
                ]);
            }

            // Calculate totals
            $sale->calculateTotals();
            $sale->save();

            // إنشاء تحصيل تلقائي للمبيعات النقدية
            if ($validated['payment_type'] === 'cash' && $sale->total_amount > 0) {
                $payment = Payment::create([
                    'payment_number' => Payment::generatePaymentNumber(Payment::TYPE_RECEIVED),
                    'payable_type' => Sale::class,
                    'payable_id' => $sale->id,
                    'sale_id' => $sale->id,
                    'type' => Payment::TYPE_RECEIVED,
                    'amount' => $sale->total_amount,
                    'method' => Payment::METHOD_CASH,
                    'payment_date' => $validated['invoice_date'],
                    'branch_id' => $branchId,
                    'user_id' => auth()->id(),
                    'sales_rep_id' => $salesRepId,
                    'status' => Payment::STATUS_COMPLETED,
                    'notes' => 'تحصيل نقدي تلقائي - فاتورة رقم ' . $sale->invoice_number,
                ]);

                // تحديث المبلغ المدفوع في الفاتورة
                $sale->paid_amount = $sale->total_amount;
                $sale->remaining_amount = 0;
                $sale->payment_status = Sale::PAYMENT_STATUS_PAID;
                $sale->save();

                // إضافة للخزينة إذا كان مندوب
                if ($salesRepId) {
                    $salesRep = SalesRep::find($salesRepId);
                    if ($salesRep) {
                        $salesRep->recordCollection($sale->total_amount, 'تحصيل نقدي - فاتورة ' . $sale->invoice_number, $payment->id);
                    }
                }
            }

            // إنشاء تحصيل للدفعة المقدمة في المبيعات الآجلة
            $advancePayment = floatval($validated['advance_payment'] ?? 0);
            if ($validated['payment_type'] === 'credit' && $advancePayment > 0 && $advancePayment <= $sale->total_amount) {
                $paymentMethod = $validated['advance_payment_method'] ?? 'cash';

                $payment = Payment::create([
                    'payment_number' => Payment::generatePaymentNumber(Payment::TYPE_RECEIVED),
                    'payable_type' => Customer::class,
                    'payable_id' => $validated['customer_id'],
                    'sale_id' => $sale->id,
                    'type' => Payment::TYPE_RECEIVED,
                    'amount' => $advancePayment,
                    'method' => $paymentMethod,
                    'payment_date' => $validated['invoice_date'],
                    'branch_id' => $branchId,
                    'user_id' => auth()->id(),
                    'sales_rep_id' => $salesRepId,
                    'status' => Payment::STATUS_COMPLETED,
                    'notes' => 'دفعة مقدمة - فاتورة رقم ' . $sale->invoice_number,
                ]);

                // تحديث المبلغ المدفوع في الفاتورة
                $sale->paid_amount = $advancePayment;
                $sale->remaining_amount = $sale->total_amount - $advancePayment;
                $sale->payment_status = $advancePayment >= $sale->total_amount
                    ? Sale::PAYMENT_STATUS_PAID
                    : Sale::PAYMENT_STATUS_PARTIAL;
                $sale->save();

                // إضافة للخزينة إذا كان مندوب
                if ($salesRepId) {
                    $salesRep = SalesRep::find($salesRepId);
                    if ($salesRep) {
                        $salesRep->recordCollection($advancePayment, 'دفعة مقدمة - فاتورة ' . $sale->invoice_number, $payment->id);
                    }
                }
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'تم إنشاء الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        // التحقق من صلاحية الوصول
        if (!$sale->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الفاتورة');
        }

        $sale->load(['customer', 'branch', 'warehouse', 'salesRep', 'user', 'items.product', 'payments']);

        $supervisorPhone = '';
        $companyName = '';
        $companyLogo = '';
        $companyStamp = '';
        try {
            $settings = \DB::table('settings')
                ->whereIn('key', ['supervisor_phone', 'company_name', 'company_logo', 'company_stamp'])
                ->pluck('value', 'key');
            $supervisorPhone = $settings['supervisor_phone'] ?? '';
            $companyName = $settings['company_name'] ?? '';
            $companyLogo = $settings['company_logo'] ?? '';
            $companyStamp = $settings['company_stamp'] ?? '';
        } catch (\Exception $e) {}

        return view('sales.show', compact('sale', 'supervisorPhone', 'companyName', 'companyLogo', 'companyStamp'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        // التحقق من صلاحية الوصول
        if (!$sale->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الفاتورة');
        }

        // Only allow editing draft sales
        if ($sale->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن تعديل فاتورة تم تأكيدها');
        }

        $user = auth()->user();
        $sale->load('items.product');
        $products = Product::active()->with('unit')->get();
        $branches = Branch::where('is_active', true)->get();

        // إذا كان المستخدم مندوب مبيعات
        if ($user->isSalesRep() && $user->salesRep) {
            $salesRep = $user->salesRep;
            $customers = Customer::forSalesRep($salesRep->id)->active()->get();
            $warehouses = $salesRep->warehouses()->where('is_active', true)->get();
            $salesReps = collect();
        } else {
            $customers = Customer::active()->get();
            $warehouses = Warehouse::active()->get();
            $salesReps = SalesRep::where('is_active', true)->get();
        }

        return view('sales.edit', compact('sale', 'customers', 'products', 'warehouses', 'branches', 'salesReps'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        // التحقق من صلاحية الوصول
        if (!$sale->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الفاتورة');
        }

        // Only allow editing draft sales
        if ($sale->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن تعديل فاتورة تم تأكيدها');
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
            'shipping_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'purchase_order_number' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            // Items validation
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        // التحقق من توفر الكميات في المخزن
        $stockErrors = [];
        foreach ($validated['items'] as $index => $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->track_inventory) {
                $warehouse = Warehouse::find($validated['warehouse_id']);
                $availableStock = $warehouse ? $warehouse->getAvailableStock($product->id) : 0;

                if ($item['quantity'] > $availableStock) {
                    $stockErrors[] = "الصنف \"{$product->name}\" - الكمية المطلوبة ({$item['quantity']}) أكبر من المتاح ({$availableStock})";
                }
            }
        }

        if (!empty($stockErrors)) {
            return back()
                ->withInput()
                ->with('error', 'الكميات غير متوفرة في المخزن: ' . implode(' | ', $stockErrors));
        }

        DB::beginTransaction();

        try {
            // Get branch_id with fallback
            $branchId = $validated['branch_id']
                ?? auth()->user()->branch_id
                ?? \App\Models\Branch::where('is_main', true)->value('id')
                ?? \App\Models\Branch::where('is_active', true)->value('id');

            // Update the sale
            $sale->update([
                'customer_id' => $validated['customer_id'],
                'branch_id' => $branchId,
                'warehouse_id' => $validated['warehouse_id'],
                'sales_rep_id' => $validated['sales_rep_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'payment_type' => $validated['payment_type'],
                'discount_type' => $validated['discount_type'] ?? 'fixed',
                'discount_value' => $validated['discount_value'] ?? 0,
                'shipping_amount' => $validated['shipping_amount'] ?? 0,
                'payment_method' => $validated['payment_method'] ?? null,
                'purchase_order_number' => $validated['purchase_order_number'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
            ]);

            // Delete existing items and recreate
            $sale->items()->delete();

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                $subtotal = $item['quantity'] * $item['unit_price'];
                $discount = $item['discount_amount'] ?? 0;
                $taxAmount = $product->is_taxable ? ($subtotal - $discount) * ($product->tax_rate / 100) : 0;
                $total = $subtotal - $discount + $taxAmount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $product->cost_price,
                    'discount_amount' => $discount,
                    'tax_rate' => $product->is_taxable ? $product->tax_rate : 0,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $subtotal,
                    'total' => $total,
                ]);
            }

            // Recalculate totals
            $sale->calculateTotals();
            $sale->save();

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'تم تحديث الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        // التحقق من صلاحية الوصول
        if (!$sale->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الفاتورة');
        }

        // Only allow deleting draft sales
        if ($sale->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن حذف فاتورة تم تأكيدها');
        }

        // Check if sale has payments
        if ($sale->paid_amount > 0) {
            return back()->with('error', 'لا يمكن حذف فاتورة تم دفع جزء منها');
        }

        DB::beginTransaction();

        try {
            // Delete sale items
            $sale->items()->delete();

            // Delete the sale
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'تم حذف الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء حذف الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * Confirm a sale and deduct inventory.
     */
    public function confirm(Sale $sale)
    {
        // التحقق من صلاحية الوصول
        if (!$sale->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الفاتورة');
        }

        if ($sale->status !== Sale::STATUS_DRAFT) {
            return back()->with('error', 'هذه الفاتورة تم تأكيدها مسبقاً');
        }

        DB::beginTransaction();

        try {
            // إذا كانت الفاتورة لمندوب - خصم من مخزون المندوب
            if ($sale->sales_rep_id) {
                foreach ($sale->items as $item) {
                    SalesRepStockMovement::record(
                        $sale->sales_rep_id,
                        $item->product_id,
                        SalesRepStockMovement::TYPE_SALE,
                        $item->quantity,
                        $sale->warehouse_id,
                        $sale->id,
                        'بيع - فاتورة ' . $sale->invoice_number
                    );
                }
            } else {
                // خصم من مخزون المخزن للفواتير بدون مندوب
                foreach ($sale->items as $item) {
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
                        'reason' => 'بيع',
                    ]);
                }
            }

            // Update sale status
            $sale->update(['status' => Sale::STATUS_CONFIRMED]);

            // Update customer balance if credit sale (only remaining after any advance payment)
            if ($sale->payment_type === 'credit' && $sale->remaining_amount > 0) {
                $sale->customer->updateBalance($sale->remaining_amount);
            }

            // إيداع مبلغ المبيعات النقدية في خزينة المندوب
            if ($sale->sales_rep_id && $sale->payment_type === 'cash') {
                $salesRep = SalesRep::find($sale->sales_rep_id);
                if ($salesRep) {
                    $salesRep->deposit(
                        $sale->total_amount,
                        'مبيعات نقدية - فاتورة ' . $sale->invoice_number,
                        Sale::class,
                        $sale->id
                    );
                }
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'تم تأكيد الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء تأكيد الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF for a sale.
     */
    public function pdf(Sale $sale)
    {
        if (!$sale->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الفاتورة');
        }

        $sale->load(['customer', 'branch', 'warehouse', 'salesRep', 'user', 'items.product']);

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

        $pdf = \Barryvdh\Snappy\Facades\SnappyPdf::loadView('pdf.sale', compact('sale', 'companyName', 'companyLogo', 'companyStamp'));

        $pdf->setOption('page-size', 'A4');
        $pdf->setOption('encoding', 'UTF-8');
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);

        return $pdf->inline($sale->invoice_number . '.pdf');
    }

    /**
     * Cancel a sale.
     */
    public function cancel(Sale $sale)
    {
        // التحقق من صلاحية الوصول
        if (!$sale->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الفاتورة');
        }

        if ($sale->status === Sale::STATUS_CANCELLED) {
            return back()->with('error', 'هذه الفاتورة ملغاة مسبقاً');
        }

        if ($sale->paid_amount > 0) {
            return back()->with('error', 'لا يمكن إلغاء فاتورة تم دفع جزء منها');
        }

        DB::beginTransaction();

        try {
            // If sale was confirmed, return inventory
            if ($sale->status === Sale::STATUS_CONFIRMED) {
                // إذا كانت الفاتورة لمندوب - إرجاع لمخزون المندوب
                if ($sale->sales_rep_id) {
                    foreach ($sale->items as $item) {
                        SalesRepStockMovement::record(
                            $sale->sales_rep_id,
                            $item->product_id,
                            SalesRepStockMovement::TYPE_RETURN,
                            $item->quantity,
                            $sale->warehouse_id,
                            $sale->id,
                            'إلغاء فاتورة ' . $sale->invoice_number
                        );
                    }
                } else {
                    foreach ($sale->items as $item) {
                        StockMovement::recordMovement([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $sale->warehouse_id,
                            'type' => StockMovement::TYPE_RETURN,
                            'quantity' => $item->quantity,
                            'unit_cost' => $item->cost_price,
                            'reference_type' => Sale::class,
                            'reference_id' => $sale->id,
                            'reference_number' => $sale->invoice_number,
                            'user_id' => auth()->id(),
                            'reason' => 'إلغاء فاتورة',
                        ]);
                    }
                }

                // Reverse customer balance if credit sale
                if ($sale->payment_type === 'credit') {
                    $sale->customer->updateBalance(-$sale->total_amount);
                }

                // سحب مبلغ المبيعات النقدية من خزينة المندوب (عكس الإيداع)
                if ($sale->sales_rep_id && $sale->payment_type === 'cash') {
                    $salesRep = SalesRep::find($sale->sales_rep_id);
                    if ($salesRep && $salesRep->treasury_balance >= $sale->total_amount) {
                        $salesRep->withdraw(
                            $sale->total_amount,
                            'إلغاء فاتورة نقدية - ' . $sale->invoice_number,
                            Sale::class,
                            $sale->id
                        );
                    }
                }
            }

            // Update sale status
            $sale->update(['status' => Sale::STATUS_CANCELLED]);

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'تم إلغاء الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء إلغاء الفاتورة: ' . $e->getMessage());
        }
    }
}
