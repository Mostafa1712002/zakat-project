<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeTransaction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeTransaction::with(['employee', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $employees = Employee::active()->orderBy('name')->get();

        // Calculate totals
        $totals = [
            'salaries' => EmployeeTransaction::salaries()->sum('amount'),
            'advances' => EmployeeTransaction::advances()->sum('amount'),
        ];

        return view('employee-transactions.index', compact('transactions', 'employees', 'totals'));
    }

    public function create(Request $request)
    {
        $employees = Employee::active()->orderBy('name')->get();
        $selectedEmployee = $request->employee_id;
        $selectedType = $request->type ?? 'salary';

        return view('employee-transactions.create', compact('employees', 'selectedEmployee', 'selectedType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:salary,advance,bonus,deduction',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'month_year' => 'nullable|string|max:7',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['transaction_number'] = EmployeeTransaction::generateTransactionNumber();
        $validated['created_by'] = auth()->id();

        // Auto-set month_year for salary type if not provided
        if ($validated['type'] === 'salary' && empty($validated['month_year'])) {
            $validated['month_year'] = date('Y-m', strtotime($validated['transaction_date']));
        }

        DB::beginTransaction();
        try {
            $transaction = EmployeeTransaction::create($validated);
            $employee = Employee::find($validated['employee_id']);

            // إنشاء مصروف تلقائي للمرتبات والسلف والمكافآت
            if (in_array($validated['type'], ['salary', 'advance', 'bonus'])) {
                // تحديد تصنيف المصروف
                $categoryCode = match($validated['type']) {
                    'salary' => 'SAL',
                    'advance' => 'ADV',
                    'bonus' => 'SAL', // المكافآت تحت المرتبات
                    default => 'GEN',
                };
                $category = ExpenseCategory::where('code', $categoryCode)->first();

                // عنوان المصروف
                $typeNames = EmployeeTransaction::TYPES;
                $title = $typeNames[$validated['type']] . ' - ' . $employee->name;
                if ($validated['type'] === 'salary' && !empty($validated['month_year'])) {
                    $title .= ' (' . $validated['month_year'] . ')';
                }

                Expense::create([
                    'expense_number' => Expense::generateExpenseNumber(),
                    'title' => $title,
                    'description' => $validated['description'] ?? null,
                    'amount' => $validated['amount'],
                    'tax_amount' => 0,
                    'total_amount' => $validated['amount'],
                    'expense_date' => $validated['transaction_date'],
                    'expense_category_id' => $category?->id,
                    'payment_method' => $validated['payment_method'],
                    'reference_number' => $validated['reference_number'],
                    'status' => 'paid',
                    'user_id' => auth()->id(),
                    'employee_id' => $employee->id,
                    'employee_transaction_id' => $transaction->id,
                    'notes' => 'مصروف تلقائي من معاملات الموظفين - ' . $transaction->transaction_number,
                ]);
            }

            DB::commit();

            $typeNames = EmployeeTransaction::TYPES;
            $message = "تم تسجيل {$typeNames[$validated['type']]} بنجاح";

            return redirect()->route('employee-transactions.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }

    public function show(EmployeeTransaction $employeeTransaction)
    {
        $employeeTransaction->load(['employee', 'creator']);
        return view('employee-transactions.show', compact('employeeTransaction'));
    }

    public function edit(EmployeeTransaction $employeeTransaction)
    {
        $employees = Employee::active()->orderBy('name')->get();
        return view('employee-transactions.edit', compact('employeeTransaction', 'employees'));
    }

    public function update(Request $request, EmployeeTransaction $employeeTransaction)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:salary,advance,bonus,deduction',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'month_year' => 'nullable|string|max:7',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Auto-set month_year for salary type if not provided
        if ($validated['type'] === 'salary' && empty($validated['month_year'])) {
            $validated['month_year'] = date('Y-m', strtotime($validated['transaction_date']));
        }

        DB::beginTransaction();
        try {
            $employeeTransaction->update($validated);
            $employee = Employee::find($validated['employee_id']);

            // تحديث المصروف المرتبط إن وجد
            $expense = Expense::where('employee_transaction_id', $employeeTransaction->id)->first();

            if (in_array($validated['type'], ['salary', 'advance', 'bonus'])) {
                $categoryCode = match($validated['type']) {
                    'salary' => 'SAL',
                    'advance' => 'ADV',
                    'bonus' => 'SAL',
                    default => 'GEN',
                };
                $category = ExpenseCategory::where('code', $categoryCode)->first();

                $typeNames = EmployeeTransaction::TYPES;
                $title = $typeNames[$validated['type']] . ' - ' . $employee->name;
                if ($validated['type'] === 'salary' && !empty($validated['month_year'])) {
                    $title .= ' (' . $validated['month_year'] . ')';
                }

                if ($expense) {
                    // تحديث المصروف الموجود
                    $expense->update([
                        'title' => $title,
                        'description' => $validated['description'] ?? null,
                        'amount' => $validated['amount'],
                        'tax_amount' => 0,
                        'total_amount' => $validated['amount'],
                        'expense_date' => $validated['transaction_date'],
                        'expense_category_id' => $category?->id,
                        'payment_method' => $validated['payment_method'],
                        'reference_number' => $validated['reference_number'],
                        'employee_id' => $employee->id,
                    ]);
                } else {
                    // إنشاء مصروف جديد
                    Expense::create([
                        'expense_number' => Expense::generateExpenseNumber(),
                        'title' => $title,
                        'description' => $validated['description'] ?? null,
                        'amount' => $validated['amount'],
                        'tax_amount' => 0,
                        'total_amount' => $validated['amount'],
                        'expense_date' => $validated['transaction_date'],
                        'expense_category_id' => $category?->id,
                        'payment_method' => $validated['payment_method'],
                        'reference_number' => $validated['reference_number'],
                        'status' => 'paid',
                        'user_id' => auth()->id(),
                        'employee_id' => $employee->id,
                        'employee_transaction_id' => $employeeTransaction->id,
                        'notes' => 'مصروف تلقائي من معاملات الموظفين - ' . $employeeTransaction->transaction_number,
                    ]);
                }
            } elseif ($expense) {
                // إذا تغير النوع لخصم، حذف المصروف
                $expense->delete();
            }

            DB::commit();
            return redirect()->route('employee-transactions.index')->with('success', 'تم تحديث المعاملة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(EmployeeTransaction $employeeTransaction)
    {
        DB::beginTransaction();
        try {
            // حذف المصروف المرتبط إن وجد
            Expense::where('employee_transaction_id', $employeeTransaction->id)->delete();

            $employeeTransaction->delete();

            DB::commit();
            return redirect()->route('employee-transactions.index')->with('success', 'تم حذف المعاملة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    // Employee-specific transactions view
    public function employeeHistory(Employee $employee, Request $request)
    {
        $query = $employee->transactions()
            ->with('creator')
            ->orderBy('transaction_date', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Calculate employee totals
        $totals = [
            'salaries' => $employee->transactions()->salaries()->sum('amount'),
            'advances' => $employee->transactions()->advances()->sum('amount'),
            'bonuses' => $employee->transactions()->where('type', 'bonus')->sum('amount'),
            'deductions' => $employee->transactions()->where('type', 'deduction')->sum('amount'),
        ];

        return view('employee-transactions.employee-history', compact('employee', 'transactions', 'totals'));
    }
}
