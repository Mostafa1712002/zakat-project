<?php

namespace App\Http\Controllers;

use App\Models\SalesRep;
use App\Models\SalesRepInventory;
use App\Models\SalesRepStockMovement;
use App\Models\SalesRepTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesRepAccountController extends Controller
{
    /**
     * ====================================
     * خزينة المندوب - للمندوب
     * ====================================
     */

    /**
     * عرض خزينة المندوب الحالي
     */
    public function myTreasury()
    {
        $user = auth()->user();
        $salesRep = $user->salesRep;

        if (!$salesRep) {
            abort(403, 'ليس لديك حساب مندوب');
        }

        $transactions = $salesRep->treasuryTransactions()
            ->with('createdByUser')
            ->latest()
            ->paginate(20);

        // إحصائيات اليوم
        $todayStats = [
            'collections' => $salesRep->treasuryTransactions()
                ->where('type', SalesRepTransaction::TYPE_COLLECTION)
                ->whereDate('created_at', today())
                ->sum('amount'),
            'expenses' => $salesRep->treasuryTransactions()
                ->where('type', SalesRepTransaction::TYPE_EXPENSE)
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];

        return view('sales-rep-account.treasury', compact('salesRep', 'transactions', 'todayStats'));
    }

    /**
     * ====================================
     * مصروفات المندوب
     * ====================================
     */

    /**
     * عرض مصروفات المندوب
     */
    public function myExpenses()
    {
        $user = auth()->user();
        $salesRep = $user->salesRep;

        if (!$salesRep) {
            abort(403, 'ليس لديك حساب مندوب');
        }

        $expenses = $salesRep->expenses()
            ->with('category')
            ->latest()
            ->paginate(20);

        $categories = ExpenseCategory::where('is_active', true)->get();

        // إحصائيات الشهر
        $monthlyTotal = $salesRep->expenses()
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('total_amount');

        return view('sales-rep-account.expenses', compact('salesRep', 'expenses', 'categories', 'monthlyTotal'));
    }

    /**
     * إنشاء مصروف جديد
     */
    public function storeExpense(Request $request)
    {
        $user = auth()->user();
        $salesRep = $user->salesRep;

        if (!$salesRep) {
            abort(403, 'ليس لديك حساب مندوب');
        }

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // التحقق من توفر الرصيد
        if ($validated['amount'] > $salesRep->treasury_balance) {
            return back()->withInput()->with('error', 'رصيد الخزينة غير كافي');
        }

        DB::transaction(function () use ($salesRep, $validated, $user) {
            // إنشاء المصروف
            $expense = Expense::create([
                'expense_number' => Expense::generateExpenseNumber(),
                'expense_category_id' => $validated['expense_category_id'],
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'tax_amount' => 0,
                'total_amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'payment_method' => 'cash',
                'status' => Expense::STATUS_PAID,
                'user_id' => $user->id,
                'sales_rep_id' => $salesRep->id,
                'notes' => $validated['notes'],
            ]);

            // خصم من خزينة المندوب
            $salesRep->recordExpense(
                $validated['amount'],
                $validated['title'],
                $expense->id
            );
        });

        return redirect()->route('sales-rep.expenses')
            ->with('success', 'تم تسجيل المصروف بنجاح');
    }

    /**
     * ====================================
     * مخزون المندوب
     * ====================================
     */

    /**
     * عرض مخزون المندوب
     */
    public function myInventory()
    {
        $user = auth()->user();
        $salesRep = $user->salesRep;

        if (!$salesRep) {
            abort(403, 'ليس لديك حساب مندوب');
        }

        $inventory = $salesRep->inventory()
            ->with('product.category')
            ->get();

        $stockMovements = $salesRep->stockMovements()
            ->with(['product', 'warehouse', 'createdBy'])
            ->latest()
            ->take(20)
            ->get();

        return view('sales-rep-account.inventory', compact('salesRep', 'inventory', 'stockMovements'));
    }

    /**
     * ====================================
     * إدارة خزينة المندوب - للأدمن
     * ====================================
     */

    /**
     * عرض قائمة خزينات المندوبين (للأدمن)
     */
    public function treasuryIndex()
    {
        $salesReps = SalesRep::where('is_active', true)
            ->with(['branch', 'user'])
            ->get();

        $totalBalance = $salesReps->sum('treasury_balance');

        return view('admin.sales-rep-treasury.index', compact('salesReps', 'totalBalance'));
    }

    /**
     * عرض تفاصيل خزينة مندوب (للأدمن)
     */
    public function treasuryShow(SalesRep $salesRep)
    {
        $transactions = $salesRep->treasuryTransactions()
            ->with('createdByUser')
            ->latest()
            ->paginate(30);

        return view('admin.sales-rep-treasury.show', compact('salesRep', 'transactions'));
    }

    /**
     * سحب رصيد خزينة المندوب للخزينة الرئيسية (للأدمن)
     */
    public function withdrawToMain(Request $request, SalesRep $salesRep)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01|max:' . $salesRep->treasury_balance,
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = $validated['amount'] ?? $salesRep->treasury_balance;

        if ($amount <= 0) {
            return back()->with('error', 'لا يوجد رصيد للسحب');
        }

        if ($amount > $salesRep->treasury_balance) {
            return back()->with('error', 'المبلغ أكبر من الرصيد المتاح');
        }

        DB::transaction(function () use ($salesRep, $amount, $validated) {
            $salesRep->withdraw(
                $amount,
                $validated['notes'] ?? 'سحب للخزينة الرئيسية بواسطة الإدارة'
            );
        });

        return redirect()->route('admin.sales-rep-treasury.index')
            ->with('success', 'تم سحب ' . number_format($amount, 2) . ' ج.م من خزينة ' . $salesRep->name);
    }

    /**
     * ====================================
     * إدارة مخزون المندوب - للأدمن
     * ====================================
     */

    /**
     * عرض قائمة مخازن المندوبين (للأدمن)
     */
    public function inventoryIndex()
    {
        $salesReps = SalesRep::where('is_active', true)
            ->withCount('inventory')
            ->with('branch')
            ->get();

        return view('admin.sales-rep-inventory.index', compact('salesReps'));
    }

    /**
     * عرض مخزون مندوب معين (للأدمن)
     */
    public function inventoryShow(SalesRep $salesRep)
    {
        $inventory = $salesRep->inventory()
            ->with('product.category')
            ->get();

        // عرض جميع الأصناف النشطة (السماح بإعادة تخصيص صنف موجود)
        $products = Product::where('is_active', true)->get();

        $warehouses = Warehouse::where('is_active', true)->get();

        $stockMovements = $salesRep->stockMovements()
            ->with(['product', 'warehouse', 'createdBy'])
            ->latest()
            ->take(30)
            ->get();

        return view('admin.sales-rep-inventory.show', compact('salesRep', 'inventory', 'products', 'warehouses', 'stockMovements'));
    }

    /**
     * تخصيص أصناف للمندوب (للأدمن)
     */
    public function allocateStock(Request $request, SalesRep $salesRep)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // التحقق من توفر الكمية في المخزن
        $warehouseStock = InventoryLevel::where('warehouse_id', $validated['warehouse_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if (!$warehouseStock || $warehouseStock->available_quantity < $validated['quantity']) {
            return back()->withInput()->with('error', 'الكمية المطلوبة غير متوفرة في المخزن');
        }

        DB::transaction(function () use ($salesRep, $validated, $warehouseStock) {
            // خصم من المخزن الرئيسي
            $warehouseStock->decrement('quantity', $validated['quantity']);

            // إضافة لمخزون المندوب
            $salesRep->allocateStock(
                $validated['product_id'],
                $validated['quantity'],
                $validated['warehouse_id'],
                $validated['notes']
            );
        });

        return back()->with('success', 'تم تخصيص الكمية للمندوب بنجاح');
    }

    /**
     * استرجاع أصناف من المندوب للمخزن (للأدمن)
     */
    public function returnStock(Request $request, SalesRep $salesRep)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // التحقق من توفر الكمية عند المندوب
        $repInventory = $salesRep->inventory()
            ->where('product_id', $validated['product_id'])
            ->first();

        if (!$repInventory || $repInventory->available_quantity < $validated['quantity']) {
            return back()->withInput()->with('error', 'الكمية المطلوبة غير متوفرة عند المندوب');
        }

        DB::transaction(function () use ($salesRep, $validated) {
            // خصم من مخزون المندوب
            SalesRepStockMovement::record(
                $salesRep->id,
                $validated['product_id'],
                SalesRepStockMovement::TYPE_OUT,
                $validated['quantity'],
                $validated['warehouse_id'],
                null,
                $validated['notes'] ?? 'إرجاع للمخزن'
            );

            // إضافة للمخزن الرئيسي
            $warehouseStock = InventoryLevel::firstOrCreate(
                [
                    'warehouse_id' => $validated['warehouse_id'],
                    'product_id' => $validated['product_id'],
                ],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );
            $warehouseStock->increment('quantity', $validated['quantity']);
        });

        return back()->with('success', 'تم إرجاع الكمية للمخزن بنجاح');
    }
}
