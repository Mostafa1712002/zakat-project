<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\SalesRep;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::with(['branch', 'salesRep'])
            ->latest()
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $salesReps = SalesRep::where('is_active', true)->get();

        return view('customers.create', compact('branches', 'salesReps'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:customers,code',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'type' => 'required|in:retail,wholesale,corporate',
            'price_tier' => 'nullable|in:retail,wholesale,special',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'sales_rep_id' => 'nullable|exists:sales_reps,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['current_balance'] = 0;
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
        $branches = Branch::where('is_active', true)->get();
        $salesReps = SalesRep::where('is_active', true)->get();

        return view('customers.edit', compact('customer', 'branches', 'salesReps'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:customers,code,' . $customer->id,
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'type' => 'required|in:retail,wholesale,corporate',
            'price_tier' => 'nullable|in:retail,wholesale,special',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
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
}
