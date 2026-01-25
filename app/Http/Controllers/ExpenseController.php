<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePaymentMethod;
use Illuminate\Http\Request;

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

        $categories = ExpenseCategory::active()->orderBy('name')->get();

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
        ]);

        $paymentMethod = null;
        if (!empty($validated['expense_payment_method_id'])) {
            $paymentMethod = ExpensePaymentMethod::find($validated['expense_payment_method_id']);
        }

        $validated['payment_method'] = $this->resolveLegacyPaymentMethod($paymentMethod);

        // Generate expense number using model method (includes soft-deleted records)
        $validated['expense_number'] = Expense::generateExpenseNumber();
        $validated['total_amount'] = $validated['amount'];
        $validated['user_id'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'تم إضافة المصروف بنجاح');
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

        return view('expenses.edit', compact('expense', 'paymentMethods', 'categories'));
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
