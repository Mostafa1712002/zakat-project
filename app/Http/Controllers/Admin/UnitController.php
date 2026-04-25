<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Models\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Unit CRUD.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 3.3)
 */
class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $query = Unit::query()->withCount('services');

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $units = $query->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.units.index', compact('units'));
    }

    public function create(): View
    {
        return view('admin.units.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Unit::create($data);

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'تم إضافة الوحدة بنجاح');
    }

    public function edit(Unit $unit): View
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $this->validateData($request);

        $unit->update($data);

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'تم تحديث الوحدة بنجاح');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->services()->exists()) {
            return redirect()
                ->route('admin.units.index')
                ->with('error', 'لا يمكن حذف الوحدة لأنها مرتبطة بخدمات قائمة');
        }

        $unit->delete();

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'تم حذف الوحدة');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
