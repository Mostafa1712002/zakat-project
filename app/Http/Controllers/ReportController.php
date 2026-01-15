<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\InventoryLevel;
use App\Models\StockMovement;
use App\Models\SalesRep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display profits report.
     */
    public function profits(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Get sales with profit calculations
        $salesData = Sale::with(['customer', 'items.product'])
            ->where('status', Sale::STATUS_CONFIRMED)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        // Calculate totals
        $totalRevenue = $salesData->sum('total_amount');
        $totalCost = $salesData->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                return $item->cost_price * $item->quantity;
            });
        });
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Group by date for chart
        $profitsByDate = $salesData->groupBy(function ($sale) {
            return $sale->invoice_date->format('Y-m-d');
        })->map(function ($sales) {
            $revenue = $sales->sum('total_amount');
            $cost = $sales->sum(function ($sale) {
                return $sale->items->sum(function ($item) {
                    return $item->cost_price * $item->quantity;
                });
            });
            return [
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $revenue - $cost,
            ];
        });

        // Top profitable products
        $profitableProducts = SaleItem::select('product_id')
            ->selectRaw('SUM((unit_price - cost_price) * quantity) as total_profit')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', Sale::STATUS_CONFIRMED)
                    ->whereBetween('invoice_date', [$startDate, $endDate]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->with('product')
            ->get();

        return view('reports.profits', compact(
            'startDate',
            'endDate',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'profitMargin',
            'profitsByDate',
            'profitableProducts',
            'salesData'
        ));
    }

    /**
     * Display sales report.
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $customerId = $request->get('customer_id');
        $status = $request->get('status');

        // Build query
        $query = Sale::with(['customer', 'branch', 'user'])
            ->whereDate('invoice_date', '>=', $startDate)
            ->whereDate('invoice_date', '<=', $endDate);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $sales = $query->latest('invoice_date')->get();

        // Summary statistics
        $totalSales = $sales->count();
        $totalAmount = $sales->where('status', '!=', Sale::STATUS_CANCELLED)->sum('total_amount');
        $paidAmount = $sales->sum('paid_amount');
        $remainingAmount = $sales->where('status', '!=', Sale::STATUS_CANCELLED)->sum('remaining_amount');

        // Sales by status
        $salesByStatus = $sales->groupBy('status')->map->count();

        // Sales by payment status
        $salesByPaymentStatus = $sales->groupBy('payment_status')->map->count();

        // Sales by customer
        $salesByCustomer = $sales->groupBy('customer_id')->map(function ($customerSales) {
            return [
                'count' => $customerSales->count(),
                'total' => $customerSales->where('status', '!=', Sale::STATUS_CANCELLED)->sum('total_amount'),
                'customer' => $customerSales->first()->customer,
            ];
        })->sortByDesc('total')->take(10);

        // Daily sales for chart
        $dailySales = $sales->where('status', '!=', Sale::STATUS_CANCELLED)
            ->groupBy(function ($sale) {
                return $sale->invoice_date->format('Y-m-d');
            })
            ->map(function ($daySales) {
                return [
                    'count' => $daySales->count(),
                    'total' => $daySales->sum('total_amount'),
                ];
            });

        // Get customers for filter dropdown
        $customers = Customer::active()->get();

        return view('reports.sales', compact(
            'startDate',
            'endDate',
            'customerId',
            'status',
            'sales',
            'totalSales',
            'totalAmount',
            'paidAmount',
            'remainingAmount',
            'salesByStatus',
            'salesByPaymentStatus',
            'salesByCustomer',
            'dailySales',
            'customers'
        ));
    }

    /**
     * Display inventory report.
     */
    public function inventory(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $categoryId = $request->get('category_id');
        $stockStatus = $request->get('stock_status'); // low, out, normal

        // Build query
        $query = InventoryLevel::with(['product.category', 'product.unit', 'warehouse'])
            ->join('products', 'inventory_levels.product_id', '=', 'products.id')
            ->select('inventory_levels.*');

        if ($warehouseId) {
            $query->where('inventory_levels.warehouse_id', $warehouseId);
        }

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        if ($stockStatus === 'low') {
            $query->whereRaw('inventory_levels.quantity <= products.min_stock')
                ->where('inventory_levels.quantity', '>', 0);
        } elseif ($stockStatus === 'out') {
            $query->where('inventory_levels.quantity', '<=', 0);
        }

        $inventoryLevels = $query->get();

        // Summary statistics
        $totalProducts = $inventoryLevels->unique('product_id')->count();
        $totalQuantity = $inventoryLevels->sum('quantity');
        $totalValue = $inventoryLevels->sum(function ($level) {
            return $level->quantity * $level->product->cost_price;
        });
        $lowStockCount = $inventoryLevels->filter(function ($level) {
            return $level->quantity > 0 && $level->quantity <= $level->product->min_stock;
        })->count();
        $outOfStockCount = $inventoryLevels->filter(function ($level) {
            return $level->quantity <= 0;
        })->count();

        // Stock by warehouse
        $stockByWarehouse = $inventoryLevels->groupBy('warehouse_id')->map(function ($warehouseStock) {
            return [
                'warehouse' => $warehouseStock->first()->warehouse,
                'total_quantity' => $warehouseStock->sum('quantity'),
                'total_value' => $warehouseStock->sum(function ($level) {
                    return $level->quantity * $level->product->cost_price;
                }),
                'products_count' => $warehouseStock->count(),
            ];
        });

        // Stock by category
        $stockByCategory = $inventoryLevels->groupBy('product.category_id')->map(function ($categoryStock) {
            return [
                'category' => $categoryStock->first()->product->category,
                'total_quantity' => $categoryStock->sum('quantity'),
                'total_value' => $categoryStock->sum(function ($level) {
                    return $level->quantity * $level->product->cost_price;
                }),
                'products_count' => $categoryStock->unique('product_id')->count(),
            ];
        });

        // Get warehouses and categories for filters
        $warehouses = Warehouse::active()->get();
        $categories = \App\Models\Category::active()->get();

        return view('reports.inventory', compact(
            'warehouseId',
            'categoryId',
            'stockStatus',
            'inventoryLevels',
            'totalProducts',
            'totalQuantity',
            'totalValue',
            'lowStockCount',
            'outOfStockCount',
            'stockByWarehouse',
            'stockByCategory',
            'warehouses',
            'categories'
        ));
    }

    /**
     * Display customer report.
     */
    public function customers(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Get customers with sales statistics
        $customers = Customer::withCount(['sales' => function ($q) use ($startDate, $endDate) {
            $q->where('status', Sale::STATUS_CONFIRMED)
                ->whereBetween('invoice_date', [$startDate, $endDate]);
        }])
            ->withSum(['sales' => function ($q) use ($startDate, $endDate) {
                $q->where('status', Sale::STATUS_CONFIRMED)
                    ->whereBetween('invoice_date', [$startDate, $endDate]);
            }], 'total_amount')
            ->orderByDesc('sales_sum_total_amount')
            ->get();

        // Summary statistics
        $totalCustomers = $customers->count();
        $activeCustomers = $customers->where('sales_count', '>', 0)->count();
        $totalSalesAmount = $customers->sum('sales_sum_total_amount');
        $averageSalePerCustomer = $activeCustomers > 0 ? $totalSalesAmount / $activeCustomers : 0;

        // Customers with outstanding balance
        $customersWithBalance = Customer::where('current_balance', '>', 0)
            ->orderByDesc('current_balance')
            ->get();

        $totalOutstanding = $customersWithBalance->sum('current_balance');

        return view('reports.customers', compact(
            'startDate',
            'endDate',
            'customers',
            'totalCustomers',
            'activeCustomers',
            'totalSalesAmount',
            'averageSalePerCustomer',
            'customersWithBalance',
            'totalOutstanding'
        ));
    }

    /**
     * Display stock movements report.
     */
    public function stockMovements(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $warehouseId = $request->get('warehouse_id');
        $productId = $request->get('product_id');
        $type = $request->get('type');

        // Build query
        $query = StockMovement::with(['product', 'warehouse', 'toWarehouse', 'user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($warehouseId) {
            $query->where(function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                    ->orWhere('to_warehouse_id', $warehouseId);
            });
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $movements = $query->latest()->paginate(50);

        // Summary by type
        $summaryByType = StockMovement::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        // Get warehouses and products for filters
        $warehouses = Warehouse::active()->get();
        $products = Product::active()->get();

        return view('reports.stock-movements', compact(
            'startDate',
            'endDate',
            'warehouseId',
            'productId',
            'type',
            'movements',
            'summaryByType',
            'warehouses',
            'products'
        ));
    }

    /**
     * عرض تقرير أداء جميع المندوبين
     */
    public function salesRepsPerformance(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // الحصول على جميع المندوبين مع إحصائياتهم
        $salesReps = SalesRep::with(['user', 'branch'])
            ->withCount(['customers', 'sales' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate]);
            }])
            ->withSum(['sales' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate])
                    ->where('status', '!=', Sale::STATUS_CANCELLED);
            }], 'total_amount')
            ->withSum(['payments' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('payment_date', [$startDate, $endDate])
                    ->where('status', 'completed');
            }], 'amount')
            ->orderByDesc('sales_sum_total_amount')
            ->get();

        // إحصائيات عامة
        $totalSalesReps = $salesReps->count();
        $activeSalesReps = $salesReps->where('sales_count', '>', 0)->count();
        $totalSales = $salesReps->sum('sales_sum_total_amount');
        $totalCollections = $salesReps->sum('payments_sum_amount');
        $totalCustomers = $salesReps->sum('customers_count');

        // أفضل المندوبين
        $topSalesReps = $salesReps->take(5);

        return view('reports.sales-reps-performance', compact(
            'startDate',
            'endDate',
            'salesReps',
            'totalSalesReps',
            'activeSalesReps',
            'totalSales',
            'totalCollections',
            'totalCustomers',
            'topSalesReps'
        ));
    }

    /**
     * عرض تقرير تفصيلي لمندوب محدد
     */
    public function salesRepDetail(Request $request, SalesRep $salesRep)
    {
        $year = $request->get('year', Carbon::now()->year);

        // تحميل العلاقات
        $salesRep->load(['user', 'branch', 'warehouses']);

        // إحصائيات شهرية
        $monthlySales = collect();
        for ($i = 1; $i <= 12; $i++) {
            $start = Carbon::create($year, $i, 1)->startOfMonth();
            $end = Carbon::create($year, $i, 1)->endOfMonth();

            $monthlySales->push([
                'month' => $i,
                'month_name' => $start->translatedFormat('F'),
                'sales' => $salesRep->sales()
                    ->whereBetween('invoice_date', [$start, $end])
                    ->where('status', '!=', Sale::STATUS_CANCELLED)
                    ->sum('total_amount'),
                'collections' => $salesRep->payments()
                    ->whereBetween('payment_date', [$start, $end])
                    ->where('status', 'completed')
                    ->sum('amount'),
                'invoices_count' => $salesRep->sales()
                    ->whereBetween('invoice_date', [$start, $end])
                    ->count(),
                'new_customers' => $salesRep->customers()
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ]);
        }

        // أفضل العملاء
        $topCustomers = $salesRep->customers()
            ->withSum(['sales' => function ($q) use ($year) {
                $q->whereYear('invoice_date', $year)
                    ->where('status', '!=', Sale::STATUS_CANCELLED);
            }], 'total_amount')
            ->orderByDesc('sales_sum_total_amount')
            ->limit(10)
            ->get();

        // آخر الفواتير
        $recentSales = $salesRep->sales()
            ->with('customer')
            ->latest('invoice_date')
            ->limit(10)
            ->get();

        // نسبة التحصيل
        $totalSalesAmount = $monthlySales->sum('sales');
        $totalCollectionsAmount = $monthlySales->sum('collections');
        $collectionRate = $totalSalesAmount > 0
            ? ($totalCollectionsAmount / $totalSalesAmount) * 100
            : 0;

        // تحقيق الهدف
        $targetAchievement = $salesRep->sales_target > 0
            ? ($totalSalesAmount / $salesRep->sales_target) * 100
            : 0;

        return view('reports.sales-rep-detail', compact(
            'salesRep',
            'year',
            'monthlySales',
            'topCustomers',
            'recentSales',
            'totalSalesAmount',
            'totalCollectionsAmount',
            'collectionRate',
            'targetAchievement'
        ));
    }
}
