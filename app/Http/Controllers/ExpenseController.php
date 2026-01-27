<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeTransaction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePaymentMethod;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    private const LEGACY_METHODS = [
        'cash',
        'bank_transfer',
        'check',
        'card',
        'other',
    ];

    public function index(Request $request)
    {
        $query = Expense::with(['paymentMethod', 'category'])
            ->orderBy('expense_date', 'desc');

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $categories = ExpenseCategory::active()->orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        $paymentMethods = ExpensePaymentMethod::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // استبعاد فئات الموظفين والشركاء - يتم إنشاؤها تلقائياً من صفحات الموظفين والشركاء
        $categories = ExpenseCategory::active()
            ->whereNotIn('code', ['SAL', 'ADV', 'EMP_ADVANCE', 'PARTNER_PROFIT'])
            ->orderBy('name')
            ->get();

        return view('expenses.create', compact('paymentMethods', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'expense_payment_method_id' => 'nullable|exists:expense_payment_methods,id',
            'vendor_name' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'employee_id' => 'nullable|exists:employees,id',
            'partner_id' => 'nullable|exists:partners,id',
            'transaction_type' => 'nullable|string', // salary, advance, bonus, deduction, withdrawal, profit_share
        ]);

        $paymentMethod = null;
        if (!empty($validated['expense_payment_method_id'])) {
            $paymentMethod = ExpensePaymentMethod::find($validated['expense_payment_method_id']);
        }

        $validated['payment_method'] = $this->resolveLegacyPaymentMethod($paymentMethod);
        $validated['expense_number'] = Expense::generateExpenseNumber();
        $validated['total_amount'] = $validated['amount'];
        $validated['user_id'] = auth()->id();

        DB::beginTransaction();

        try {
            // إنشاء معاملة موظف إذا تم اختيار موظف
            if (!empty($validated['employee_id'])) {
                $transactionType = $validated['transaction_type'] ?? 'salary';
                $employeeTransaction = EmployeeTransaction::create([
                    'employee_id' => $validated['employee_id'],
                    'transaction_number' => EmployeeTransaction::generateTransactionNumber(),
                    'type' => $transactionType,
                    'amount' => $validated['amount'],
                    'transaction_date' => $validated['expense_date'],
                    'month_year' => date('Y-m', strtotime($validated['expense_date'])),
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['notes'] ?? null,
                ]);
                $validated['employee_transaction_id'] = $employeeTransaction->id;
            }

            // إنشاء معاملة شريك إذا تم اختيار شريك
            if (!empty($validated['partner_id'])) {
                $transactionType = $validated['transaction_type'] ?? 'withdrawal';
                $partnerTransaction = PartnerTransaction::create([
                    'partner_id' => $validated['partner_id'],
                    'transaction_number' => PartnerTransaction::generateTransactionNumber(),
                    'type' => $transactionType,
                    'amount' => $validated['amount'],
                    'transaction_date' => $validated['expense_date'],
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['notes'] ?? null,
                ]);
                $validated['partner_transaction_id'] = $partnerTransaction->id;
            }

            // إزالة الحقول غير الموجودة في جدول expenses
            unset($validated['transaction_type']);

            Expense::create($validated);

            DB::commit();

            return redirect()->route('expenses.index')->with('success', 'تم إضافة المصروف بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function show(Expense $expense)
    {
        $expense->load('paymentMethod');
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $paymentMethods = ExpensePaymentMethod::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $partners = Partner::where('is_active', true)->orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'paymentMethods', 'categories', 'employees', 'partners'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'expense_payment_method_id' => 'nullable|exists:expense_payment_methods,id',
            'vendor_name' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $paymentMethod = null;
        if (!empty($validated['expense_payment_method_id'])) {
            $paymentMethod = ExpensePaymentMethod::find($validated['expense_payment_method_id']);
        }

        $validated['payment_method'] = $this->resolveLegacyPaymentMethod($paymentMethod);
        $validated['total_amount'] = $validated['amount'];

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'تم تحديث المصروف بنجاح');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'تم حذف المصروف بنجاح');
    }

    private function resolveLegacyPaymentMethod(?ExpensePaymentMethod $paymentMethod): string
    {
        if (!$paymentMethod) {
            return 'cash';
        }

        return in_array($paymentMethod->code, self::LEGACY_METHODS, true)
            ? $paymentMethod->code
            : 'other';
    }
}
