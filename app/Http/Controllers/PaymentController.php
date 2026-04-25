<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * NOTE: Phase 1 cleanup — Payment is being repurposed for Phase 6 (Treasury).
 * Original collect/pay flows depended on deleted Sale/Purchase/Supplier/SalesRep models.
 * Customer collection and supplier payment flows will be re-built in Phase 6
 * against new Invoice/Quote models. Phase 4 removed the legacy collectFromCustomer
 * methods because they referenced the deprecated App\Models\Customer.
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
