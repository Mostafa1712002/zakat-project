<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['branch', 'user'])->orderBy('name');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('employee_code', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('job_title', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('has_account')) {
            if ($request->has_account == '1') {
                $query->whereNotNull('user_id');
            } else {
                $query->whereNull('user_id');
            }
        }

        $employees = $query->paginate(20);
        $departments = Employee::distinct()->pluck('department')->filter();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // جلب المستخدمين الذين لديهم دور employee ولم يتم ربطهم بموظف بعد
        $availableUsers = User::role('employee')
            ->whereDoesntHave('employee')
            ->where('is_active', true)
            ->get();

        return view('employees.create', compact('branches', 'availableUsers'));
    }

    public function store(Request $request)
    {
        // التحقق من نوع الإنشاء: مستخدم موجود أو جديد أو بدون حساب
        $createAccount = $request->input('create_account', false);
        $createNewUser = $request->input('create_new_user', false);
        $existingUserId = $request->input('existing_user_id');

        // قواعد التحقق الأساسية
        $rules = [
            'name' => 'required|string|max:255',
            'employee_code' => 'nullable|string|max:50|unique:employees,employee_code',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'national_id' => 'nullable|digits:14',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|in:0,1,true,false',
            'notes' => 'nullable|string',
        ];

        // إضافة قواعد التحقق حسب نوع الحساب
        if ($createAccount) {
            if ($createNewUser || empty($existingUserId)) {
                // إنشاء مستخدم جديد
                $rules['email'] = 'required|email|max:255|unique:users,email';
                $rules['password'] = 'required|string|min:8|confirmed';
            } else {
                // استخدام مستخدم موجود
                $rules['existing_user_id'] = 'required|exists:users,id';
            }
        }

        $validated = $request->validate($rules);

        // توليد كود الموظف إذا لم يتم إدخاله
        if (empty($validated['employee_code'])) {
            $lastEmployee = Employee::withTrashed()
                ->where('employee_code', 'like', 'EMP-%')
                ->orderBy('employee_code', 'desc')
                ->first();

            if ($lastEmployee) {
                $lastNumber = (int) substr($lastEmployee->employee_code, 4);
                $validated['employee_code'] = 'EMP-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $validated['employee_code'] = 'EMP-0001';
            }
        }

        // تعيين الحالة
        $validated['is_active'] = $request->input('is_active', 1) == 1;

        DB::beginTransaction();

        try {
            $userId = null;

            if ($createAccount) {
                if ($createNewUser || empty($existingUserId)) {
                    // إنشاء حساب مستخدم جديد
                    $user = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => Hash::make($validated['password']),
                        'branch_id' => $validated['branch_id'] ?? null,
                        'phone' => $validated['phone'] ?? null,
                        'is_active' => $validated['is_active'],
                    ]);

                    // تعيين صلاحية الموظف
                    $role = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
                    $user->assignRole($role);

                    $userId = $user->id;
                } else {
                    // استخدام المستخدم الموجود
                    $user = User::findOrFail($validated['existing_user_id']);
                    $user->update([
                        'branch_id' => $validated['branch_id'] ?? $user->branch_id,
                        'is_active' => $validated['is_active'],
                    ]);

                    $userId = $user->id;
                }
            }

            // إنشاء سجل الموظف
            $employeeData = [
                'user_id' => $userId,
                'name' => $validated['name'],
                'employee_code' => $validated['employee_code'],
                'email' => $validated['email'] ?? ($createAccount && !$createNewUser && $existingUserId ? User::find($existingUserId)?->email : null),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'department' => $validated['department'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'hire_date' => $validated['hire_date'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ];

            Employee::create($employeeData);

            DB::commit();

            $message = $createAccount
                ? 'تم إضافة الموظف بنجاح وتم إنشاء حساب تسجيل الدخول'
                : 'تم إضافة الموظف بنجاح';

            return redirect()->route('employees.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء الموظف: ' . $e->getMessage());
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['branch', 'user', 'transactions' => function ($q) {
            $q->latest('transaction_date')->limit(10);
        }]);

        // إحصائيات الموظف
        $stats = [
            'total_salaries' => $employee->total_salaries_paid,
            'total_advances' => $employee->total_advances,
            'balance' => $employee->balance,
            'years_of_service' => $employee->years_of_service,
        ];

        return view('employees.show', compact('employee', 'stats'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // جلب المستخدمين المتاحين
        $availableUsers = User::role('employee')
            ->where(function ($query) use ($employee) {
                $query->whereDoesntHave('employee')
                      ->orWhere('id', $employee->user_id);
            })
            ->where('is_active', true)
            ->get();

        return view('employees.edit', compact('employee', 'branches', 'availableUsers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $createAccount = $request->input('create_account', false);
        $createNewUser = $request->input('create_new_user', false);
        $existingUserId = $request->input('existing_user_id');
        $removeAccount = $request->input('remove_account', false);

        // قواعد التحقق الأساسية
        $rules = [
            'name' => 'required|string|max:255',
            'employee_code' => 'nullable|string|max:50|unique:employees,employee_code,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'national_id' => 'nullable|digits:14',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'hire_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|in:0,1,true,false',
            'notes' => 'nullable|string',
        ];

        // إذا كان لديه حساب مسبقاً أو سيتم إنشاء حساب جديد
        if ($employee->user_id || $createAccount) {
            if ($createNewUser) {
                $rules['email'] = 'required|email|max:255|unique:users,email';
                $rules['password'] = 'required|string|min:8|confirmed';
            } elseif ($employee->user_id && !$removeAccount) {
                $rules['email'] = 'required|email|max:255|unique:users,email,' . $employee->user_id;
                $rules['password'] = 'nullable|string|min:8|confirmed';
            } elseif ($createAccount && $existingUserId) {
                $rules['existing_user_id'] = 'required|exists:users,id';
            }
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->input('is_active', 1) == 1;

        DB::beginTransaction();

        try {
            $userId = $employee->user_id;

            // إزالة الحساب إذا تم طلب ذلك
            if ($removeAccount && $employee->user_id) {
                $userId = null;
            }
            // تحديث الحساب الموجود
            elseif ($employee->user_id && !$createNewUser) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'branch_id' => $validated['branch_id'] ?? null,
                    'is_active' => $validated['is_active'],
                ];

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $employee->user->update($userData);
            }
            // إنشاء حساب جديد
            elseif ($createAccount) {
                if ($createNewUser) {
                    $user = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => Hash::make($validated['password']),
                        'branch_id' => $validated['branch_id'] ?? null,
                        'phone' => $validated['phone'] ?? null,
                        'is_active' => $validated['is_active'],
                    ]);

                    $role = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
                    $user->assignRole($role);

                    $userId = $user->id;
                } elseif ($existingUserId) {
                    $user = User::findOrFail($existingUserId);
                    $user->update([
                        'branch_id' => $validated['branch_id'] ?? $user->branch_id,
                        'is_active' => $validated['is_active'],
                    ]);

                    $userId = $user->id;
                }
            }

            // تحديث سجل الموظف
            $employee->update([
                'user_id' => $userId,
                'name' => $validated['name'],
                'employee_code' => $validated['employee_code'],
                'email' => $validated['email'] ?? $employee->email,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'department' => $validated['department'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'hire_date' => $validated['hire_date'] ?? null,
                'termination_date' => $validated['termination_date'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الموظف: ' . $e->getMessage());
        }
    }

    public function destroy(Employee $employee)
    {
        // التحقق من وجود معاملات
        if ($employee->transactions()->exists()) {
            return back()->with('error', 'لا يمكن حذف الموظف لأنه لديه معاملات مالية مرتبطة');
        }

        DB::beginTransaction();

        try {
            // لا نحذف حساب المستخدم، فقط نفك الارتباط
            $employee->update(['user_id' => null]);
            $employee->delete();

            DB::commit();

            return redirect()->route('employees.index')->with('success', 'تم حذف الموظف بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف الموظف: ' . $e->getMessage());
        }
    }
}
