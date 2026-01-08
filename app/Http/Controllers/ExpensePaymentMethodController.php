<?php

namespace App\Http\Controllers;

use App\Models\ExpensePaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpensePaymentMethodController extends Controller
{
    public function index()
    {
        $methods = ExpensePaymentMethod::orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('expense-payment-methods.index', compact('methods'));
    }

    public function create()
    {
        return view('expense-payment-methods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50|alpha_dash|unique:expense_payment_methods,code',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if (empty($validated['code'])) {
            do {
                $validated['code'] = 'method_' . Str::lower(Str::random(6));
            } while (ExpensePaymentMethod::where('code', $validated['code'])->exists());
        }

        ExpensePaymentMethod::create($validated);

        return redirect()->route('expense-payment-methods.index')
            ->with('success', 'تم إضافة طريقة الدفع بنجاح');
    }

    public function edit(ExpensePaymentMethod $expensePaymentMethod)
    {
        return view('expense-payment-methods.edit', compact('expensePaymentMethod'));
    }

    public function update(Request $request, ExpensePaymentMethod $expensePaymentMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|alpha_dash|unique:expense_payment_methods,code,' . $expensePaymentMethod->id,
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $expensePaymentMethod->update($validated);

        return redirect()->route('expense-payment-methods.index')
            ->with('success', 'تم تحديث طريقة الدفع بنجاح');
    }

    public function destroy(ExpensePaymentMethod $expensePaymentMethod)
    {
        if ($expensePaymentMethod->expenses()->exists()) {
            return back()->with('error', 'لا يمكن حذف طريقة دفع مرتبطة بمصروفات');
        }

        $expensePaymentMethod->delete();

        return redirect()->route('expense-payment-methods.index')
            ->with('success', 'تم حذف طريقة الدفع بنجاح');
    }
}
