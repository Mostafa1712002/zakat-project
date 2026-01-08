<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
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

        // Monthly stats
        $monthlySales = Sale::where('invoice_date', '>=', $monthStart)->sum('total_amount');
        $monthlyPurchases = Purchase::where('invoice_date', '>=', $monthStart)->sum('total_amount');
        $monthlyExpenses = Expense::where('expense_date', '>=', $monthStart)->sum('amount');
        $monthlyProfit = $monthlySales - $monthlyPurchases - $monthlyExpenses;

        // Collection stats
        $unpaidInvoices = Sale::where('payment_status', 'unpaid')->count();
        $overdueInvoices = Sale::where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->count();
        $expectedPayments = Sale::where('payment_status', '!=', 'paid')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->sum('remaining_amount');

        $stats = [
            'total_sales' => $todaySales,
            'total_purchases' => $todayPurchases,
            'low_stock_count' => Product::where('stock', '<', 10)->count(),
            'monthly_profit' => $monthlyProfit,
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

        $low_stock_products = Product::where('stock', '<', 20)
            ->orderBy('stock')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recent_sales', 'top_customers', 'low_stock_products'));
    }
}
