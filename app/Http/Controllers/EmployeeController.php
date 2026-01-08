<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('branch')->orderBy('name');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('employee_code', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('job_title', 'like', "%{$request->search}%");
            });
        }

        if ($request->department) {
            $query->where('department', $request->department);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $employees = $query->paginate(20);
        $departments = Employee::distinct()->pluck('department')->filter();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('employees.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_code' => 'nullable|string|max:50|unique:employees,employee_code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'national_id' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['employee_code'])) {
            $validated['employee_code'] = 'EMP-' . str_pad(Employee::count() + 1, 4, '0', STR_PAD_LEFT);
        }

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function show(Employee $employee)
    {
        $employee->load('branch');
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('employees.edit', compact('employee', 'branches'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_code' => 'nullable|string|max:50|unique:employees,employee_code,' . $employee->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'national_id' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'hire_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف بنجاح');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'تم حذف الموظف بنجاح');
    }
}
