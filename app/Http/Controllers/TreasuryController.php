<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SalesRep;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TreasuryController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // === الوارد (المقبوضات) ===

        // 1. التحصيلات من العملاء (excluding rep withdrawals)
        $collections = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where(function ($q) {
                $q->where('payable_type', '!=', SalesRep::class)
                  ->orWhereNull('payable_type');
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // 2. المبيعات النقدية (الكاش المباشر)
        $cashSales = Sale::where('payment_type', 'cash')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');

        // 3. سحب خزينات المندوبين
        $repWithdrawals = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where('payable_type', SalesRep::class)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $totalIncome = $collections + $cashSales + $repWithdrawals;

        // === الصادر (المدفوعات) ===
        // المصروفات تشمل: مصروفات عامة + مشتريات نقدية + مدفوعات موردين (كلها Expense records)
        $totalExpenses = Expense::where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // === الربح (من المبيعات المؤكدة في الفترة) ===
        $periodSales = Sale::with('items')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->where('is_quotation', false)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        $profit = $periodSales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                return ($item->unit_price - ($item->cost_price ?? 0)) * $item->quantity;
            });
        });

        // === تفاصيل المصروفات حسب الفئة ===
        $expensesByCategory = Expense::with('category')
            ->where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($e) => $e->category?->name ?? 'بدون فئة')
            ->map(fn($items) => $items->sum('amount'))
            ->sortByDesc(fn($v) => $v);

        // === آخر الحركات ===
        $recentTransactions = collect();

        // إضافة التحصيلات (excluding rep withdrawals)
        $recentCollections = Payment::with('payable')
            ->where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where(function ($q) {
                $q->where('payable_type', '!=', SalesRep::class)
                  ->orWhereNull('payable_type');
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->latest('payment_date')
            ->take(10)
            ->get()
            ->map(fn($p) => [
                'date' => $p->payment_date,
                'type' => 'income',
                'description' => 'تحصيل من ' . ($p->payable?->name ?? 'عميل'),
                'amount' => $p->amount,
                'method' => $p->method,
            ]);

        // إضافة سحب خزينات المندوبين
        $recentRepWithdrawals = Payment::with('payable')
            ->where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where('payable_type', SalesRep::class)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->latest('payment_date')
            ->take(10)
            ->get()
            ->map(fn($p) => [
                'date' => $p->payment_date,
                'type' => 'income',
                'description' => 'سحب خزينة مندوب: ' . ($p->payable?->name ?? 'مندوب'),
                'amount' => $p->amount,
                'method' => $p->method,
            ]);

        // إضافة المبيعات النقدية
        $recentCashSales = Sale::with('customer')
            ->where('payment_type', 'cash')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->latest('invoice_date')
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'date' => $s->invoice_date,
                'type' => 'income',
                'description' => 'مبيعات نقدية - ' . $s->invoice_number,
                'amount' => $s->total_amount,
                'method' => 'cash',
            ]);

        // إضافة المصروفات
        $recentExpenses = Expense::with('category')
            ->where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->latest('expense_date')
            ->take(10)
            ->get()
            ->map(fn($e) => [
                'date' => $e->expense_date,
                'type' => 'expense',
                'description' => $e->title . ' (' . ($e->category?->name ?? 'عام') . ')',
                'amount' => $e->amount,
                'method' => $e->payment_method ?? 'cash',
            ]);

        // دمج وترتيب الحركات
        $recentTransactions = $recentCollections
            ->concat($recentRepWithdrawals)
            ->concat($recentCashSales)
            ->concat($recentExpenses)
            ->sortByDesc('date')
            ->take(15)
            ->values();

        // === الرصيد الكلي (من بداية النظام) ===
        $totalCollectionsAll = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where(function ($q) {
                $q->where('payable_type', '!=', SalesRep::class)
                  ->orWhereNull('payable_type');
            })
            ->sum('amount');

        $totalRepWithdrawalsAll = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where('payable_type', SalesRep::class)
            ->sum('amount');

        $totalCashSalesAll = Sale::where('payment_type', 'cash')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->sum('total_amount');

        $totalExpensesAll = Expense::where('status', 'paid')->sum('amount');

        // رأس المال من الشركاء (الاستثمار المبدئي + استثمارات إضافية - مرتجعات رأس مال)
        $openingBalance = Partner::sum('initial_investment')
            + PartnerTransaction::where('type', PartnerTransaction::TYPE_INVESTMENT)->sum('amount')
            - PartnerTransaction::where('type', PartnerTransaction::TYPE_RETURN)->sum('amount');

        $overallBalance = $openingBalance + ($totalCollectionsAll + $totalCashSalesAll + $totalRepWithdrawalsAll) - $totalExpensesAll;

        // === الفواتير المستحقة والمتأخرة ===
        $overdueInvoices = Sale::with('customer')
            ->where('status', '!=', Sale::STATUS_CANCELLED)
            ->whereIn('payment_status', ['unpaid', 'partial', 'overdue'])
            ->where(function($q) {
                $q->where('due_date', '<', now())
                  ->orWhere('payment_status', 'overdue');
            })
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $totalOverdueAmount = Sale::where('status', '!=', Sale::STATUS_CANCELLED)
            ->whereIn('payment_status', ['unpaid', 'partial', 'overdue'])
            ->where(function($q) {
                $q->where('due_date', '<', now())
                  ->orWhere('payment_status', 'overdue');
            })
            ->sum('remaining_amount');

        // فواتير مستحقة قريباً (خلال 7 أيام)
        $upcomingDueInvoices = Sale::with('customer')
            ->where('status', '!=', Sale::STATUS_CANCELLED)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date')
            ->take(10)
            ->get();

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
