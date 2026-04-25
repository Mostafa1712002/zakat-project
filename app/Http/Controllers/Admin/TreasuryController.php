<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Treasury\Models\Treasury;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class TreasuryController extends Controller
{
    public function index()
    {
        $treasuries = Treasury::with('branch')->orderBy('name')->paginate(25);

        return view('admin.treasuries.index', compact('treasuries'));
    }

    public function create()
    {
        $branches = Branch::all();

        return view('admin.treasuries.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'type' => ['required', 'in:cash,bank'],
            'balance' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['balance'] = $data['balance'] ?? 0;

        Treasury::create($data);

        return redirect()
            ->route('admin.treasuries.index')
            ->with('success', 'تم إضافة الخزينة');
    }

    public function edit(Treasury $treasury)
    {
        $branches = Branch::all();

        return view('admin.treasuries.edit', compact('treasury', 'branches'));
    }

    public function update(Request $request, Treasury $treasury)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'type' => ['required', 'in:cash,bank'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $treasury->update($data);

        return redirect()
            ->route('admin.treasuries.index')
            ->with('success', 'تم تحديث الخزينة');
    }

    public function destroy(Treasury $treasury)
    {
        if ($treasury->payments()->exists()) {
            return back()->with('error', 'لا يمكن حذف خزينة فيها معاملات');
        }

        $treasury->delete();

        return redirect()
            ->route('admin.treasuries.index')
            ->with('success', 'تم حذف الخزينة');
    }
}
