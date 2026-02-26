<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SalesRep;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\CommissionWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SalesRepController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesRep::with(['user', 'branch', 'warehouses'])
            ->withCount(['customers', 'sales']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $salesReps = $query->orderBy('name')->paginate(20);

        return view('sales-reps.index', compact('salesReps'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        // جلب المستخدمين الذين لديهم دور sales_rep ولم يتم ربطهم بمندوب بعد
        $availableUsers = User::role('sales_rep')
            ->whereDoesntHave('salesRep')
            ->where('is_active', true)
            ->get();

        return view('sales-reps.create', compact('branches', 'warehouses', 'availableUsers'));
    }

    public function store(Request $request)
    {
        // التحقق من نوع الإنشاء: مستخدم موجود أو جديد
        $createNewUser = $request->input('create_new_user', false);
        $existingUserId = $request->input('existing_user_id');

        // قواعد التحقق حسب النوع
        $rules = [
            'code' => 'nullable|string|max:50|unique:sales_reps,code',
            'type' => 'nullable|in:fridge,special',
            'phone' => 'nullable|string|max:20',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_type' => 'nullable|in:percentage,fixed',
            'sales_target' => 'nullable|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ];

        if ($createNewUser || empty($existingUserId)) {
            // إنشاء مستخدم جديد
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|email|max:255|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            // استخدام مستخدم موجود
            $rules['existing_user_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        // Auto-generate sales rep code if not provided
        if (empty($validated['code'])) {
            do {
                $validated['code'] = 'REP-' . date('Ymd') . '-' . rand(100, 999);
            } while (SalesRep::where('code', $validated['code'])->exists());
        }

        DB::beginTransaction();

        try {
            if ($createNewUser || empty($existingUserId)) {
                // إنشاء حساب المستخدم الجديد
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'branch_id' => $validated['branch_id'],
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                // تعيين صلاحية المندوب
                $role = Role::firstOrCreate(['name' => 'sales_rep', 'guard_name' => 'web']);
                $user->assignRole($role);

                $name = $validated['name'];
                $email = $validated['email'];
            } else {
                // استخدام المستخدم الموجود
                $user = User::findOrFail($validated['existing_user_id']);
                $user->update([
                    'branch_id' => $validated['branch_id'],
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                $name = $user->name;
                $email = $user->email;
            }

            // إنشاء سجل المندوب
            $salesRep = SalesRep::create([
                'user_id' => $user->id,
                'name' => $name,
                'code' => $validated['code'],
                'type' => $validated['type'] ?? null,
                'phone' => $validated['phone'] ?? $user->phone,
                'email' => $email,
                'commission_rate' => $validated['commission_rate'] ?? 0,
                'commission_type' => $validated['commission_type'] ?? 'percentage',
                'sales_target' => $validated['sales_target'] ?? 0,
                'branch_id' => $validated['branch_id'],
                'is_active' => $validated['is_active'] ?? true,
                'notes' => $validated['notes'] ?? null,
            ]);

            // ربط المخازن
            if (!empty($validated['warehouse_ids'])) {
                $warehouseSync = [];
                foreach ($validated['warehouse_ids'] as $warehouseId) {
                    $warehouseSync[$warehouseId] = [
                        'is_default' => $warehouseId == ($validated['default_warehouse_id'] ?? $validated['warehouse_ids'][0])
                    ];
                }
                $salesRep->warehouses()->sync($warehouseSync);
            }

            DB::commit();

            $message = $createNewUser || empty($existingUserId)
                ? 'تم إضافة المندوب بنجاح وتم إنشاء حساب تسجيل الدخول'
                : 'تم إضافة المندوب بنجاح وتم ربطه بالمستخدم الموجود';

            return redirect()->route('sales-reps.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء المندوب: ' . $e->getMessage());
        }
    }

    public function show(SalesRep $salesRep)
    {
        $salesRep->load(['user', 'branch', 'warehouses', 'customers', 'sales' => function ($q) {
            $q->latest()->limit(10);
        }]);

        // إحصائيات المندوب
        $stats = [
            'total_customers' => $salesRep->customers()->count(),
            'total_sales' => $salesRep->sales()->sum('total_amount'),
            'total_invoices' => $salesRep->sales()->count(),
            'total_collections' => $salesRep->payments()->sum('amount'),
            'target_achievement' => $salesRep->target_achievement,
        ];

        // معاملات الخزينة الأخيرة
        $treasuryTransactions = $salesRep->treasuryTransactions()
            ->with('creator')
            ->latest()
            ->limit(10)
            ->get();

        // مصروفات المندوب الأخيرة
        $expenses = $salesRep->expenses()
            ->with('category')
            ->latest()
            ->limit(10)
            ->get();

        return view('sales-reps.show', compact('salesRep', 'stats', 'treasuryTransactions', 'expenses'));
    }

    /**
     * عرض صفحة إيداع في الخزينة
     */
    public function showDepositForm(SalesRep $salesRep)
    {
        return view('sales-reps.treasury.deposit', compact('salesRep'));
    }

    /**
     * إيداع مبلغ في خزينة المندوب
     */
    public function deposit(Request $request, SalesRep $salesRep)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $salesRep->deposit($validated['amount'], $validated['description']);

        return redirect()->route('sales-reps.show', $salesRep)
            ->with('success', 'تم إيداع المبلغ بنجاح');
    }

    /**
     * عرض صفحة سحب من الخزينة
     */
    public function showWithdrawForm(SalesRep $salesRep)
    {
        return view('sales-reps.treasury.withdraw', compact('salesRep'));
    }

    /**
     * سحب مبلغ من خزينة المندوب
     */
    public function withdraw(Request $request, SalesRep $salesRep)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $salesRep->treasury_balance,
            'description' => 'nullable|string|max:255',
        ]);

        $salesRep->withdraw($validated['amount'], $validated['description']);

        return redirect()->route('sales-reps.show', $salesRep)
            ->with('success', 'تم سحب المبلغ بنجاح');
    }

    /**
     * عرض كشف حساب الخزينة
     */
    public function treasuryStatement(SalesRep $salesRep)
    {
        $transactions = $salesRep->treasuryTransactions()
            ->with('creator')
            ->latest()
            ->paginate(20);

        return view('sales-reps.treasury.statement', compact('salesRep', 'transactions'));
    }

    public function edit(SalesRep $salesRep)
    {
        $salesRep->load(['user', 'warehouses']);
        $branches = Branch::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('sales-reps.edit', compact('salesRep', 'branches', 'warehouses'));
    }

    public function update(Request $request, SalesRep $salesRep)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:sales_reps,code,' . $salesRep->id,
            'type' => 'nullable|in:fridge,special',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email,' . $salesRep->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_type' => 'nullable|in:percentage,fixed',
            'sales_target' => 'nullable|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // تحديث حساب المستخدم إذا وجد
            if ($salesRep->user) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'branch_id' => $validated['branch_id'],
                    'is_active' => $validated['is_active'] ?? true,
                ];

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $salesRep->user->update($userData);
            }

            // تحديث سجل المندوب
            $salesRep->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'type' => $validated['type'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'commission_rate' => $validated['commission_rate'] ?? 0,
                'commission_type' => $validated['commission_type'] ?? 'percentage',
                'sales_target' => $validated['sales_target'] ?? 0,
                'branch_id' => $validated['branch_id'],
                'is_active' => $validated['is_active'] ?? true,
                'notes' => $validated['notes'],
            ]);

            // تحديث المخازن
            if (isset($validated['warehouse_ids'])) {
                $warehouseSync = [];
                foreach ($validated['warehouse_ids'] as $warehouseId) {
                    $warehouseSync[$warehouseId] = [
                        'is_default' => $warehouseId == ($validated['default_warehouse_id'] ?? ($validated['warehouse_ids'][0] ?? null))
                    ];
                }
                $salesRep->warehouses()->sync($warehouseSync);
            } else {
                $salesRep->warehouses()->detach();
            }

            DB::commit();

            return redirect()->route('sales-reps.index')
                ->with('success', 'تم تحديث المندوب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث المندوب: ' . $e->getMessage());
        }
    }

    public function destroy(SalesRep $salesRep)
    {
        // التحقق من وجود عملاء أو مبيعات
        if ($salesRep->customers()->exists()) {
            return back()->with('error', 'لا يمكن حذف المندوب لأنه لديه عملاء مرتبطين');
        }

        if ($salesRep->sales()->exists()) {
            return back()->with('error', 'لا يمكن حذف المندوب لأنه لديه فواتير مرتبطة');
        }

        DB::beginTransaction();

        try {
            // حذف المخازن المرتبطة
            $salesRep->warehouses()->detach();

            // حذف حساب المستخدم
            if ($salesRep->user) {
                $salesRep->user->delete();
            }

            // حذف المندوب
            $salesRep->delete();

            DB::commit();

            return redirect()->route('sales-reps.index')
                ->with('success', 'تم حذف المندوب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف المندوب: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة سحب عمولة المندوب
     */
    public function showWithdrawCommission(SalesRep $salesRep)
    {
        $year = request('year', now()->year);
        $month = request('month', now()->month);

        $commissionDetails = $salesRep->getMonthlyCommissionDetails($year, $month);

        // التحقق من أن العمولة لم تُسحب من قبل
        $alreadyWithdrawn = CommissionWithdrawal::isAlreadyWithdrawn($salesRep->id, $year, $month);

        if ($alreadyWithdrawn) {
            return back()->with('error', 'تم صرف عمولة هذا الشهر مسبقاً');
        }

        if (!$commissionDetails['has_met_target']) {
            return back()->with('error', 'المندوب لم يحقق التارجت لهذا الشهر بعد');
        }

        if ($commissionDetails['commission_earned'] <= 0) {
            return back()->with('error', 'لا توجد عمولة مستحقة للسحب');
        }

        return view('sales-reps.withdraw-commission', compact('salesRep', 'commissionDetails', 'year', 'month'));
    }

    /**
     * تنفيذ سحب عمولة المندوب
     */
    public function withdrawCommission(Request $request, SalesRep $salesRep)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'month' => 'required|integer|min:1|max:12',
            'notes' => 'nullable|string|max:500',
        ]);

        // التحقق من أن العمولة لم تُسحب من قبل
        if (CommissionWithdrawal::isAlreadyWithdrawn($salesRep->id, $validated['year'], $validated['month'])) {
            return back()->with('error', 'تم صرف عمولة هذا الشهر مسبقاً');
        }

        $commissionDetails = $salesRep->getMonthlyCommissionDetails($validated['year'], $validated['month']);

        if (!$commissionDetails['has_met_target']) {
            return back()->with('error', 'المندوب لم يحقق التارجت لهذا الشهر');
        }

        if ($validated['amount'] > $commissionDetails['commission_earned']) {
            return back()->with('error', 'المبلغ المطلوب أكبر من العمولة المستحقة');
        }

        DB::transaction(function () use ($salesRep, $validated) {
            // البحث عن أو إنشاء فئة مصروفات تارجت المندوبين
            $category = ExpenseCategory::firstOrCreate(
                ['code' => 'sales-rep-commission'],
                ['name' => 'تارجت مندوب', 'is_active' => true]
            );

            $monthName = \Carbon\Carbon::create($validated['year'], $validated['month'], 1)->translatedFormat('F Y');

            // إنشاء المصروف
            $expense = Expense::create([
                'expense_number' => Expense::generateExpenseNumber(),
                'expense_category_id' => $category->id,
                'title' => 'عمولة مندوب: ' . $salesRep->name,
                'description' => 'صرف عمولة للمندوب ' . $salesRep->name . ' عن شهر ' . $monthName,
                'amount' => $validated['amount'],
                'tax_amount' => 0,
                'total_amount' => $validated['amount'],
                'expense_date' => now(),
                'payment_method' => 'cash',
                'status' => Expense::STATUS_PAID,
                'user_id' => auth()->id(),
                'sales_rep_id' => $salesRep->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            // تسجيل أن العمولة تم سحبها لهذا الشهر
            CommissionWithdrawal::create([
                'sales_rep_id' => $salesRep->id,
                'expense_id' => $expense->id,
                'year' => $validated['year'],
                'month' => $validated['month'],
                'amount' => $validated['amount'],
                'withdrawn_by' => auth()->id(),
            ]);
        });

        return redirect()->route('sales-reps.index')
            ->with('success', 'تم صرف عمولة المندوب بنجاح: ' . number_format($validated['amount'], 2) . ' ج.م');
    }
}
