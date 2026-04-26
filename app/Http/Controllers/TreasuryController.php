<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Domain\Treasury\Models\Payment;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * NOTE: Phase 1 cleanup stub.
 * Original Treasury report depended on deleted Sale/SalesRep models.
 * Will be re-built in Phase 6 (Treasury) and Phase 7 (Reports) against new Invoice model.
 */
class TreasuryController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $collections = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $cashSales = 0.0;
        $repWithdrawals = 0.0;
        $totalIncome = $collections;

        $totalExpenses = Expense::where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $profit = 0.0;

        $expensesByCategory = Expense::with('category')
            ->where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($e) => $e->category?->name ?? 'بدون فئة')
            ->map(fn($items) => $items->sum('amount'))
            ->sortByDesc(fn($v) => $v);

        $recentTransactions = collect();

        $totalCollectionsAll = Payment::sum('amount');
        $totalExpensesAll = Expense::where('status', 'paid')->sum('amount');

        $openingBalance = Partner::sum('initial_investment')
            + PartnerTransaction::where('type', PartnerTransaction::TYPE_INVESTMENT)->sum('amount')
            - PartnerTransaction::where('type', PartnerTransaction::TYPE_RETURN)->sum('amount');

        $overallBalance = $openingBalance + $totalCollectionsAll - $totalExpensesAll;

        $overdueInvoices = collect();
        $totalOverdueAmount = 0.0;
        $upcomingDueInvoices = collect();

        return view('treasury.index', compact(
            'startDate',
            'endDate',
            'collections',
            'cashSales',
            'repWithdrawals',
            'totalIncome',
            'totalExpenses',
            'profit',
            'expensesByCategory',
            'recentTransactions',
            'overallBalance',
            'openingBalance',
            'overdueInvoices',
            'totalOverdueAmount',
            'upcomingDueInvoices'
        ));
    }
}
