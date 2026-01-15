<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SalesRep;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SalesRepDashboardController extends Controller
{
    /**
     * لوحة تحكم المندوب الرئيسية
     */
    public function index()
    {
        $salesRep = $this->getSalesRep();

        if (!$salesRep) {
            abort(403, 'لم يتم ربط حسابك بمندوب مبيعات');
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // إحصائيات المندوب
        $stats = [
            'total_customers' => $salesRep->customers()->count(),
            'active_customers' => $salesRep->customers()->active()->count(),
            'total_sales' => $salesRep->sales()->sum('total_amount'),
            'monthly_sales' => $salesRep->sales()
                ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->sum('total_amount'),
            'today_sales' => $salesRep->sales()
                ->whereDate('invoice_date', $today)
                ->sum('total_amount'),
            'total_collections' => $salesRep->payments()->sum('amount'),
            'monthly_collections' => $salesRep->payments()
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            'pending_amount' => $salesRep->sales()
                ->where('payment_status', '!=', 'paid')
                ->sum('remaining_amount'),
            'sales_target' => $salesRep->sales_target,
            'target_achievement' => $salesRep->target_achievement,
        ];

        // آخر الفواتير
        $recentSales = $salesRep->sales()
            ->with('customer')
            ->latest('invoice_date')
            ->limit(5)
            ->get();

        // آخر التحصيلات
        $recentPayments = $salesRep->payments()
            ->with('payable')
            ->latest('payment_date')
            ->limit(5)
            ->get();

        // العملاء المستحق عليهم
        $pendingCustomers = $salesRep->customers()
            ->where('current_balance', '>', 0)
            ->orderByDesc('current_balance')
            ->limit(5)
            ->get();

        // المخازن المتاحة
        $warehouses = $salesRep->warehouses()->where('is_active', true)->get();

        return view('sales-rep-dashboard.index', compact(
            'salesRep',
            'stats',
            'recentSales',
            'recentPayments',
            'pendingCustomers',
            'warehouses'
        ));
    }

    /**
     * عرض قائمة عملاء المندوب
     */
    public function customers()
    {
        $salesRep = $this->getSalesRep();

        $customers = $salesRep->customers()
            ->withCount('sales')
            ->withSum('sales', 'total_amount')
            ->orderByDesc('current_balance')
            ->paginate(15);

        return view('sales-rep-dashboard.customers', compact('salesRep', 'customers'));
    }

    /**
     * عرض مبيعات المندوب
     */
    public function sales(Request $request)
    {
        $salesRep = $this->getSalesRep();

        $query = $salesRep->sales()->with(['customer', 'warehouse']);

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب حالة الدفع
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $sales = $query->latest('invoice_date')->paginate(15);

        return view('sales-rep-dashboard.sales', compact('salesRep', 'sales'));
    }

    /**
     * عرض تحصيلات المندوب
     */
    public function collections(Request $request)
    {
        $salesRep = $this->getSalesRep();

        $query = $salesRep->payments()->with('payable');

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest('payment_date')->paginate(15);

        // إجمالي التحصيلات
        $totalCollections = (clone $query)->sum('amount');

        return view('sales-rep-dashboard.collections', compact('salesRep', 'payments', 'totalCollections'));
    }

    /**
     * تقارير أداء المندوب
     */
    public function reports(Request $request)
    {
        $salesRep = $this->getSalesRep();

        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month');

        // تقرير المبيعات الشهرية
        $monthlySales = collect();
        for ($i = 1; $i <= 12; $i++) {
            $start = Carbon::create($year, $i, 1)->startOfMonth();
            $end = Carbon::create($year, $i, 1)->endOfMonth();

            $monthlySales->push([
                'month' => $i,
                'month_name' => $start->translatedFormat('F'),
                'sales' => $salesRep->sales()
                    ->whereBetween('invoice_date', [$start, $end])
                    ->sum('total_amount'),
                'collections' => $salesRep->payments()
                    ->whereBetween('payment_date', [$start, $end])
                    ->sum('amount'),
                'invoices_count' => $salesRep->sales()
                    ->whereBetween('invoice_date', [$start, $end])
                    ->count(),
            ]);
        }

        // أفضل العملاء
        $topCustomers = $salesRep->customers()
            ->withSum('sales', 'total_amount')
            ->orderByDesc('sales_sum_total_amount')
            ->limit(10)
            ->get();

        return view('sales-rep-dashboard.reports', compact(
            'salesRep',
            'year',
            'month',
            'monthlySales',
            'topCustomers'
        ));
    }

    /**
     * الحصول على المندوب الحالي
     */
    protected function getSalesRep(): ?SalesRep
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        return $user->salesRep;
    }
}
