<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

/**
 * NOTE: Phase 1 cleanup stub.
 * Original reports (sales, inventory, stock-movements, sales-reps, profits) depended on
 * deleted Sale/SaleItem/Product/Warehouse/InventoryLevel/StockMovement/SalesRep models.
 * Will be re-built in Phase 7 (Reports & Dashboard) against new Quote/Invoice models.
 */
class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function customers(Request $request)
    {
        $customers = Customer::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(20);

        return view('reports.customers', compact('customers'));
    }

    public function profits()
    {
        $profits = collect();

        return view('reports.profits', compact('profits'));
    }
}
