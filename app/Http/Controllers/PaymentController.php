<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
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
        $query = Payment::with(['payable', 'user', 'salesRep', 'branch']);

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

        return view('payments.collect-from-customer', compact('customer'));
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

            // تحديث رصيد العميل
            $customer->decrement('current_balance', $validated['amount']);

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

        return view('payments.pay-to-supplier', compact('supplier'));
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
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(Payment::TYPE_PAID),
                'payable_type' => Supplier::class,
                'payable_id' => $supplier->id,
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

        $payment->load(['payable', 'user', 'salesRep', 'branch']);

        return view('payments.show', compact('payment'));
    }
}
