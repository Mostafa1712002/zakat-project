<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Partner;
use App\Models\PartnerTransaction;

class DashboardController extends Controller
{
    /**
     * NOTE: Phase 1 cleanup stub.
     * Original dashboard depended on deleted Sale/Purchase/Product/Inventory/SalesRep models.
     * Will be rebuilt in Phase 7 (Reports & Dashboard) against new Quote/Invoice models.
     */
    public function index()
    {
        // Treasury balance — partner investment minus expenses; collections placeholder for Phase 5+.
        $openingBalance = Partner::sum('initial_investment')
            + PartnerTransaction::where('type', PartnerTransaction::TYPE_INVESTMENT)->sum('amount')
            - PartnerTransaction::where('type', PartnerTransaction::TYPE_RETURN)->sum('amount');

        $totalCollections = Payment::where('type', Payment::TYPE_RECEIVED)
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount');

        $totalExpenses = Expense::where('status', 'paid')->sum('amount');

        $treasuryBalance = $openingBalance + $totalCollections - $totalExpenses;

        $stats = [
            'total_sales' => 0,
            'total_purchases' => 0,
            'low_stock_count' => 0,
            'treasury_balance' => $treasuryBalance,
            'customers_count' => Customer::count(),
            'products_count' => 0,
            'categories_count' => 0,
            'unpaid_invoices' => 0,
            'overdue_invoices' => 0,
            'expected_payments' => 0,
        ];

        $recent_sales = collect();
        $top_customers = collect();
        $low_stock_products = collect();

        return view('dashboard', compact('stats', 'recent_sales', 'top_customers', 'low_stock_products'));
    }
}
