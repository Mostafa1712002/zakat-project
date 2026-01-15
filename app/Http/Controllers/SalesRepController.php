<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SalesRep;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SalesRepController extends Controller
{
    public function index()
    {
        $salesReps = SalesRep::with(['user', 'branch', 'warehouses'])
            ->withCount(['customers', 'sales'])
            ->orderBy('name')
            ->paginate(20);

        return view('sales-reps.index', compact('salesReps'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('sales-reps.create', compact('branches', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:sales_reps,code',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
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

        // Auto-generate sales rep code if not provided
        if (empty($validated['code'])) {
            do {
                $validated['code'] = 'REP-' . date('Ymd') . '-' . rand(100, 999);
            } while (SalesRep::where('code', $validated['code'])->exists());
        }

        DB::beginTransaction();

        try {
            // إنشاء حساب المستخدم
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'branch_id' => $validated['branch_id'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // تعيين صلاحية المندوب
            $user->assignRole('sales_rep');

            // إنشاء سجل المندوب
            $salesRep = SalesRep::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'code' => $validated['code'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'commission_rate' => $validated['commission_rate'] ?? 0,
                'commission_type' => $validated['commission_type'] ?? 'percentage',
                'sales_target' => $validated['sales_target'] ?? 0,
                'branch_id' => $validated['branch_id'],
                'is_active' => $validated['is_active'] ?? true,
                'notes' => $validated['notes'],
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

            return redirect()->route('sales-reps.index')
                ->with('success', 'تم إضافة المندوب بنجاح وتم إنشاء حساب تسجيل الدخول');

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

        return view('sales-reps.show', compact('salesRep', 'stats'));
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
}
