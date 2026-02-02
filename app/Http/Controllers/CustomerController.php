<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\SalesRep;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::with(['branch', 'salesRep'])
            ->forSalesRep()
            ->latest()
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $branches = Branch::where('is_active', true)->get();

        // المندوب لا يرى قائمة المندوبين - يتم تعيينه تلقائياً
        $salesReps = $user->isSalesRep()
            ? collect()
            : SalesRep::where('is_active', true)->get();

        $currentSalesRep = $user->salesRep;

        return view('customers.create', compact('branches', 'salesReps', 'currentSalesRep'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:50', Rule::unique('customers', 'code')->whereNull('deleted_at')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'type' => 'required|in:retail,wholesale,corporate',
            'price_tier' => 'nullable|in:retail,wholesale,special',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'target_amount' => 'nullable|numeric|min:0',
            'target_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'sales_rep_id' => 'nullable|exists:sales_reps,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['current_balance'] = 0;
        $validated['target_paid_amount'] = 0;
        if (empty($validated['price_tier'] ?? null)) {
            $validated['price_tier'] = 'retail';
        }

        // Auto-generate customer code if not provided
        if (empty($validated['code'])) {
            do {
                $validated['code'] = 'CUST-' . date('Ymd') . '-' . rand(100, 999);
            } while (Customer::where('code', $validated['code'])->exists());
        }

        // إذا كان المستخدم مندوب، يتم تعيينه تلقائياً للعميل
        $user = auth()->user();
        if ($user->isSalesRep() && $user->salesRep) {
            $validated['sales_rep_id'] = $user->salesRep->id;
        }

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'تم إضافة العميل بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        // التحقق من صلاحية الوصول
        if (!$customer->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا العميل');
        }

        $customer->load(['branch', 'salesRep', 'sales' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        // التحقق من صلاحية الوصول
        if (!$customer->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا العميل');
        }

        $user = auth()->user();
        $branches = Branch::where('is_active', true)->get();

        // المندوب لا يرى قائمة المندوبين
        $salesReps = $user->isSalesRep()
            ? collect()
            : SalesRep::where('is_active', true)->get();

        return view('customers.edit', compact('customer', 'branches', 'salesReps'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        // التحقق من صلاحية الوصول
        if (!$customer->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا العميل');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:50', Rule::unique('customers', 'code')->ignore($customer->id)->whereNull('deleted_at')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'type' => 'required|in:retail,wholesale,corporate',
            'price_tier' => 'nullable|in:retail,wholesale,special',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'target_amount' => 'nullable|numeric|min:0',
            'target_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'sales_rep_id' => 'nullable|exists:sales_reps,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        if (empty($validated['price_tier'] ?? null)) {
            $validated['price_tier'] = $customer->price_tier ?? 'retail';
        }

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // التحقق من صلاحية الوصول
        if (!$customer->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا العميل');
        }

        // Check if customer has sales
        if ($customer->sales()->exists()) {
            return back()->with('error', 'لا يمكن حذف العميل لأنه لديه فواتير مسجلة');
        }

        // Check if customer has outstanding balance
        if ($customer->current_balance > 0) {
            return back()->with('error', 'لا يمكن حذف العميل لأنه لديه رصيد مستحق');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'تم حذف العميل بنجاح');
    }

    /**
     * عرض صفحة سحب تارجت العميل
     */
    public function showWithdrawTarget(Customer $customer)
    {
        if (!$customer->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا العميل');
        }

        if (!$customer->hasAchievedTarget()) {
            return back()->with('error', 'العميل لم يحقق التارجت بعد');
        }

        if ($customer->withdrawable_target_amount <= 0) {
            return back()->with('error', 'لا يوجد رصيد تارجت متاح للسحب');
        }

        return view('customers.withdraw-target', compact('customer'));
    }

    /**
     * تنفيذ سحب تارجت العميل
     */
    public function withdrawTarget(Request $request, Customer $customer)
    {
        if (!$customer->canCurrentUserAccess()) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا العميل');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $customer->withdrawable_target_amount,
            'notes' => 'nullable|string|max:500',
        ]);

        if (!$customer->hasAchievedTarget()) {
            return back()->with('error', 'العميل لم يحقق التارجت بعد');
        }

        DB::transaction(function () use ($customer, $validated) {
            // البحث عن أو إنشاء فئة مصروفات تارجت العملاء
            $category = ExpenseCategory::firstOrCreate(
                ['code' => 'customer-target'],
                ['name' => 'تارجت عميل', 'is_active' => true]
            );

            // إنشاء المصروف
            $expense = Expense::create([
                'expense_number' => Expense::generateExpenseNumber(),
                'expense_category_id' => $category->id,
                'title' => 'تارجت عميل: ' . $customer->name,
                'description' => 'صرف خصم تارجت للعميل ' . $customer->name . ' - نسبة ' . $customer->target_discount_percentage . '%',
                'amount' => $validated['amount'],
                'tax_amount' => 0,
                'total_amount' => $validated['amount'],
                'expense_date' => now(),
                'payment_method' => 'cash',
                'status' => Expense::STATUS_PAID,
                'user_id' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // تحديث رصيد التارجت المصروف للعميل
            $customer->recordTargetWithdrawal($validated['amount']);
        });

        return redirect()->route('customers.index')
            ->with('success', 'تم صرف تارجت العميل بنجاح: ' . number_format($validated['amount'], 2) . ' ج.م');
    }
}
