<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SalesRep;
use App\Models\InventoryLevel;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        // Daily stats
        $todaySales = Sale::whereDate('invoice_date', $today)->sum('total_amount');
        $todayPurchases = Purchase::whereDate('invoice_date', $today)->sum('total_amount');

        // Monthly stats - only confirmed sales count in profit
        $monthlySales = Sale::where('invoice_date', '>=', $monthStart)
            ->where('status', Sale::STATUS_CONFIRMED)
            ->sum('total_amount');
        $monthlyPurchases = Purchase::where('invoice_date', '>=', $monthStart)->sum('total_amount');
        $monthlyExpenses = Expense::where('expense_date', '>=', $monthStart)
            ->where('status', 'paid')
            ->sum('amount');
        $monthlyProfit = $monthlySales - $monthlyPurchases - $monthlyExpenses;

        // Rep treasury withdrawals this month
        $repWithdrawals = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where('payable_type', SalesRep::class)
            ->where('payment_date', '>=', $monthStart)
            ->sum('amount');

        // Add rep withdrawals to profit (money that entered main treasury from reps)
        $monthlyProfit += $repWithdrawals;

        // Cash balance (actual money received - paid out this month)
        // Collections from customers (excluding rep withdrawals to avoid double-counting)
        $monthlyCollections = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where('payment_date', '>=', $monthStart)
            ->where(function ($q) {
                $q->where('payable_type', '!=', SalesRep::class)
                  ->orWhereNull('payable_type');
            })
            ->sum('amount');
        $monthlyCashSales = Sale::where('payment_type', 'cash')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->where('invoice_date', '>=', $monthStart)
            ->sum('total_amount');
        $monthlySupplierPayments = Payment::where('type', Payment::TYPE_PAID)
            ->where('status', Payment::STATUS_COMPLETED)
            ->where('payment_date', '>=', $monthStart)
            ->sum('amount');
        $monthlyCashBalance = ($monthlyCollections + $monthlyCashSales + $repWithdrawals) - ($monthlyExpenses + $monthlySupplierPayments);

        // Collection stats
        $unpaidInvoices = Sale::where('payment_status', 'unpaid')->count();
        $overdueInvoices = Sale::where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->count();
        $expectedPayments = Sale::where('payment_status', '!=', 'paid')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->sum('remaining_amount');

        // Low stock count - count products with inventory below min_stock
        $lowStockCount = InventoryLevel::where('quantity', '<', 10)->distinct('product_id')->count('product_id');

        $stats = [
            'total_sales' => $todaySales,
            'total_purchases' => $todayPurchases,
            'low_stock_count' => $lowStockCount,
            'monthly_profit' => $monthlyProfit,
            'monthly_cash_balance' => $monthlyCashBalance,
            'customers_count' => Customer::count(),
            'products_count' => Product::count(),
            'categories_count' => Category::count(),
            'unpaid_invoices' => $unpaidInvoices,
            'overdue_invoices' => $overdueInvoices,
            'expected_payments' => $expectedPayments,
        ];

        $recent_sales = Sale::with('customer')->latest('invoice_date')->take(5)->get();
        $top_customers = Customer::withSum('sales', 'total_amount')
            ->withCount('sales')
            ->orderByDesc('sales_sum_total_amount')
            ->take(3)
            ->get();

        // Low stock products - get products with low inventory
        $low_stock_products = Product::select('products.*')
            ->selectRaw('(SELECT COALESCE(SUM(quantity), 0) FROM inventory_levels WHERE inventory_levels.product_id = products.id) as total_stock')
            ->havingRaw('total_stock < 20')
            ->orderBy('total_stock', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recent_sales', 'top_customers', 'low_stock_products'));
    }
}
