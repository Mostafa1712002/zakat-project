<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * NOTE: Phase 1 cleanup — Payment is being repurposed for Phase 6 (Treasury).
 * Original collect/pay flows depended on deleted Sale/Purchase/Supplier/SalesRep models.
 * Customer collection and supplier payment flows will be re-built in Phase 6
 * against new Invoice/Quote models.
 */
class PaymentController extends Controller
{
    /**
     * عرض قائمة التحصيلات والمدفوعات
     */
    public function index(Request $request)
    {
        $query = Payment::with(['payable', 'user', 'branch']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

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
     * عرض نموذج تحصيل من عميل (stub — will be wired to invoices in Phase 6).
     */
    public function showCollectFromCustomer(Customer $customer)
    {
        $unpaidSales = collect();

        return view('payments.collect-from-customer', compact('customer', 'unpaidSales'));
    }

    /**
     * تحصيل من عميل (stub — will be wired to invoices in Phase 6).
     */
    public function collectFromCustomer(Request $request, Customer $customer)
    {
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
                'status' => Payment::STATUS_COMPLETED,
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'تم التحصيل بنجاح - رقم الإيصال: ' . $payment->payment_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء التحصيل: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل دفعة
     */
    public function show(Payment $payment)
    {
        $payment->load(['payable', 'user', 'branch']);

        return view('payments.show', compact('payment'));
    }

    private function getTreasuryBalance(): float
    {
        $openingBalance = Partner::sum('initial_investment')
            + PartnerTransaction::where('type', PartnerTransaction::TYPE_INVESTMENT)->sum('amount')
            - PartnerTransaction::where('type', PartnerTransaction::TYPE_RETURN)->sum('amount');

        $totalCollections = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount');

        $totalExpenses = Expense::where('status', 'paid')->sum('amount');

        return $openingBalance + $totalCollections - $totalExpenses;
    }
}
