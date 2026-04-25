<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * NOTE: Phase 1 cleanup — removed SalesRep scoping and Sale relationships.
     * Customer scope (Account Manager) is rebuilt in Phase 4 via AccountManagerScope.
     */

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['branch']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->item_type) {
            $query->where('item_type', $request->item_type);
        }

        $customers = $query->latest()->paginate(15);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $salesReps = collect();
        $currentSalesRep = null;

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
            'item_type' => 'nullable|in:' . implode(',', array_column(customer_item_types(), 'value')),
            'price_tier' => 'nullable|in:retail,wholesale,special',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ] + (feature_enabled('customer_target') ? [
            'target_amount' => 'nullable|numeric|min:0',
            'target_discount_percentage' => 'nullable|numeric|min:0|max:100',
        ] : []));

        $validated['type'] = 'retail';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['current_balance'] = 0;
        if (feature_enabled('customer_target')) {
            $validated['target_paid_amount'] = 0;
        }
        if (empty($validated['price_tier'] ?? null)) {
            $validated['price_tier'] = 'retail';
        }

        // Auto-generate customer code if not provided
        if (empty($validated['code'])) {
            do {
                $validated['code'] = 'CUST-' . date('Ymd') . '-' . rand(100, 999);
            } while (Customer::where('code', $validated['code'])->exists());
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
        $customer->load(['branch']);

        $totalRemaining = 0;

        return view('customers.show', compact('customer', 'totalRemaining'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $branches = Branch::where('is_active', true)->get();
        $salesReps = collect();

        return view('customers.edit', compact('customer', 'branches', 'salesReps'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:50', Rule::unique('customers', 'code')->ignore($customer->id)->whereNull('deleted_at')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'item_type' => 'nullable|in:' . implode(',', array_column(customer_item_types(), 'value')),
            'price_tier' => 'nullable|in:retail,wholesale,special',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ] + (feature_enabled('customer_target') ? [
            'target_amount' => 'nullable|numeric|min:0',
            'target_discount_percentage' => 'nullable|numeric|min:0|max:100',
        ] : []));

        $validated['type'] = 'retail';
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
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $customer->withdrawable_target_amount,
            'notes' => 'nullable|string|max:500',
        ]);

        if (!$customer->hasAchievedTarget()) {
            return back()->with('error', 'العميل لم يحقق التارجت بعد');
        }

        DB::transaction(function () use ($customer, $validated) {
            $category = ExpenseCategory::firstOrCreate(
                ['code' => 'customer-target'],
                ['name' => 'تارجت عميل', 'is_active' => true]
            );

            Expense::create([
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

            $customer->recordTargetWithdrawal($validated['amount']);
        });

        return redirect()->route('customers.index')
            ->with('success', 'تم صرف تارجت العميل بنجاح: ' . number_format($validated['amount'], 2) . ' ج.م');
    }
}
