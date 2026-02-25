<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SalesRep;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * عرض قائمة التحصيلات والمدفوعات
     */
    public function index(Request $request)
    {
        $query = Payment::with(['payable', 'user', 'salesRep', 'branch', 'sale', 'purchase']);

        // تصفية المندوب
        $user = auth()->user();
        if ($user->isSalesRep() && $user->salesRep) {
            $query->where('sales_rep_id', $user->salesRep->id);
        }

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest('payment_date')->paginate(20);

        return view('payments.index', compact('payments'));
    }

    /**
     * عرض نموذج تحصيل من عميل
     */
    public function showCollectFromCustomer(Customer $customer)
    {
        // التحقق من صلاحية الوصول للعميل
        if (!$customer->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا العميل');
        }

        // الفواتير غير المدفوعة بالكامل
        $unpaidSales = $customer->sales()
            ->whereIn('payment_status', ['unpaid', 'partial', 'overdue'])
            ->where('status', '!=', 'cancelled')
            ->orderBy('due_date')
            ->orderBy('invoice_date')
            ->get();

        return view('payments.collect-from-customer', compact('customer', 'unpaidSales'));
    }

    /**
     * تحصيل من عميل
     */
    public function collectFromCustomer(Request $request, Customer $customer)
    {
        // التحقق من صلاحية الوصول للعميل
        if (!$customer->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا العميل');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'sale_id' => 'nullable|exists:sales,id',
            'method' => 'required|in:cash,bank_transfer,instapay,vodafone_cash,check,card,other',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'check_number' => 'nullable|string|max:50|required_if:method,check',
            'check_date' => 'nullable|date|required_if:method,check',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $user = auth()->user();
            $salesRep = $user->salesRep;

            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(Payment::TYPE_RECEIVED),
                'payable_type' => Customer::class,
                'payable_id' => $customer->id,
                'sale_id' => $validated['sale_id'],
                'type' => Payment::TYPE_RECEIVED,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'],
                'check_number' => $validated['check_number'],
                'check_date' => $validated['check_date'],
                'bank_name' => $validated['bank_name'],
                'bank_account' => $validated['bank_account'],
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'sales_rep_id' => $salesRep?->id,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => $validated['notes'],
            ]);

            // تحديث حالة الدفع للفواتير
            if ($validated['sale_id']) {
                // تحصيل على فاتورة محددة
                $sale = \App\Models\Sale::find($validated['sale_id']);
                if ($sale) {
                    $sale->addPayment($validated['amount']);
                }
            } else {
                // تحصيل عام - توزيع المبلغ على الفواتير المستحقة (الأقدم أولاً)
                $remainingAmount = $validated['amount'];
                $unpaidSales = $customer->sales()
                    ->whereIn('payment_status', ['unpaid', 'partial', 'overdue'])
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('due_date')
                    ->orderBy('invoice_date')
                    ->get();

                foreach ($unpaidSales as $sale) {
                    if ($remainingAmount <= 0) break;

                    $applyAmount = min($remainingAmount, $sale->remaining_amount);
                    if ($applyAmount > 0) {
                        $sale->addPayment($applyAmount);
                        $remainingAmount -= $applyAmount;
                    }
                }
            }

            // إعادة حساب رصيد العميل من الفواتير الفعلية
            $customer->recalculateBalance();

            // إضافة التحصيل لخزينة المندوب تلقائياً
            if ($salesRep) {
                $salesRep->recordCollection(
                    $validated['amount'],
                    'تحصيل من العميل: ' . $customer->name,
                    $payment->id
                );
            }

            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'تم التحصيل بنجاح - رقم الإيصال: ' . $payment->payment_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء التحصيل: ' . $e->getMessage());
        }
    }

    /**
     * عرض نموذج دفع لمورد
     */
    public function showPayToSupplier(Supplier $supplier)
    {
        // التحقق من الصلاحية - فقط للأدمن والمحاسب
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasRole(['admin', 'branch_manager', 'accountant'])) {
            abort(403, 'ليس لديك صلاحية للدفع للموردين');
        }

        // الفواتير غير المدفوعة بالكامل (فقط اللي عليها مبلغ متبقي)
        $unpaidPurchases = $supplier->purchases()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('status', '!=', 'cancelled')
            ->where('remaining_amount', '>', 0)
            ->orderBy('due_date')
            ->orderBy('invoice_date')
            ->get();

        // إجمالي الرصيد المستحق الفعلي
        $totalRemaining = $unpaidPurchases->sum('remaining_amount');

        return view('payments.pay-to-supplier', compact('supplier', 'unpaidPurchases', 'totalRemaining'));
    }

    /**
     * دفع لمورد
     */
    public function payToSupplier(Request $request, Supplier $supplier)
    {
        // التحقق من الصلاحية
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasRole(['admin', 'branch_manager', 'accountant'])) {
            abort(403, 'ليس لديك صلاحية للدفع للموردين');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'purchase_id' => 'nullable|exists:purchases,id',
            'method' => 'required|in:cash,bank_transfer,instapay,vodafone_cash,check,card,other',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'check_number' => 'nullable|string|max:50|required_if:method,check',
            'check_date' => 'nullable|date|required_if:method,check',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        // التحقق من رصيد الخزنة
        $treasuryBalance = $this->getTreasuryBalance();
        if ($validated['amount'] > $treasuryBalance) {
            return back()->withInput()->with('error', 'رصيد الخزنة غير كافي. الرصيد الحالي: ' . number_format($treasuryBalance, 2) . ' ج.م، المطلوب دفعه: ' . number_format($validated['amount'], 2) . ' ج.م');
        }

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(Payment::TYPE_PAID),
                'payable_type' => Supplier::class,
                'payable_id' => $supplier->id,
                'purchase_id' => $validated['purchase_id'],
                'type' => Payment::TYPE_PAID,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'],
                'check_number' => $validated['check_number'],
                'check_date' => $validated['check_date'],
                'bank_name' => $validated['bank_name'],
                'bank_account' => $validated['bank_account'],
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => $validated['notes'],
            ]);

            // تحديث رصيد المورد
            $supplier->decrement('current_balance', $validated['amount']);

            // تحديث حالة الدفع لفاتورة الشراء إذا تم تحديدها
            if ($validated['purchase_id']) {
                $purchase = \App\Models\Purchase::find($validated['purchase_id']);
                if ($purchase && method_exists($purchase, 'addPayment')) {
                    $purchase->addPayment($validated['amount']);
                }
            }

            // تسجيل مصروف لدفعة المورد
            $purchaseCategory = ExpenseCategory::where('code', 'PURCHASE')->first()
                ?? ExpenseCategory::where('name', 'like', '%مشتريات%')->first();

            $purchaseRef = $validated['purchase_id']
                ? \App\Models\Purchase::find($validated['purchase_id'])
                : null;

            Expense::create([
                'expense_number' => Expense::generateExpenseNumber(),
                'expense_category_id' => $purchaseCategory?->id,
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'expense_date' => $validated['payment_date'],
                'title' => 'دفع للمورد: ' . $supplier->name,
                'description' => $purchaseRef
                    ? 'دفعة فاتورة شراء رقم ' . $purchaseRef->invoice_number
                    : 'دفعة للمورد ' . $supplier->name,
                'amount' => $validated['amount'],
                'total_amount' => $validated['amount'],
                'payment_method' => $validated['method'],
                'vendor_name' => $supplier->name,
                'reference_number' => $payment->payment_number,
                'status' => 'paid',
            ]);

            DB::commit();

            return redirect()->route('suppliers.show', $supplier)
                ->with('success', 'تم الدفع بنجاح - رقم الإيصال: ' . $payment->payment_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء الدفع: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل دفعة
     */
    public function show(Payment $payment)
    {
        // التحقق من صلاحية الوصول
        $user = auth()->user();
        if ($user->isSalesRep() && $user->salesRep && $payment->sales_rep_id !== $user->salesRep->id) {
            if (!$user->isSuperAdmin() && !$user->hasRole(['admin', 'branch_manager', 'accountant'])) {
                abort(403, 'ليس لديك صلاحية للوصول لهذه الدفعة');
            }
        }

        $payment->load(['payable', 'user', 'salesRep', 'branch', 'sale', 'purchase']);

        return view('payments.show', compact('payment'));
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
