<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::withCount(['users']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('manager_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $branches = $query->latest()->paginate(15);
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:branches,code',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_main' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_main'] = $validated['is_main'] ?? false;

        if (empty($validated['code'])) {
            $validated['code'] = 'BR-' . str_pad(Branch::withTrashed()->count() + 1, 3, '0', STR_PAD_LEFT);
        }

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'تم إضافة الفرع بنجاح');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_main' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_main'] = $validated['is_main'] ?? false;

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'تم تحديث الفرع بنجاح');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->users()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الفرع لأنه مرتبط بمستخدمين');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'تم حذف الفرع بنجاح');
    }
}
