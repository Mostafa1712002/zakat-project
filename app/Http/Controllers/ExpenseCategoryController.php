<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->orderBy('name')
            ->paginate(20);

        return view('expense-categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = ExpenseCategory::active()->root()->orderBy('name')->get();
        return view('expense-categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'code' => 'nullable|string|max:50|unique:expense_categories,code',
            'parent_id' => 'nullable|exists:expense_categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'تم إضافة نوع المصروف بنجاح');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $parentCategories = ExpenseCategory::active()
            ->root()
            ->where('id', '!=', $expenseCategory->id)
            ->orderBy('name')
            ->get();

        return view('expense-categories.edit', compact('expenseCategory', 'parentCategories'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'code' => 'nullable|string|max:50|unique:expense_categories,code,' . $expenseCategory->id,
            'parent_id' => 'nullable|exists:expense_categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'تم تحديث نوع المصروف بنجاح');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا النوع لأنه مرتبط بمصروفات');
        }

        if ($expenseCategory->children()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا النوع لأنه يحتوي على أنواع فرعية');
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')
            ->with('success', 'تم حذف نوع المصروف بنجاح');
    }
}
