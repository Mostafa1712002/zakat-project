<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Models\ServiceType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Service Type CRUD.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-001)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 3.3)
 */
class ServiceTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = ServiceType::query()->withCount('services');

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $serviceTypes = $query->orderBy('sort_order')->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.service-types.index', compact('serviceTypes'));
    }

    public function create(): View
    {
        return view('admin.service-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        ServiceType::create($data);

        return redirect()
            ->route('admin.service-types.index')
            ->with('success', 'تم إضافة نوع الخدمة بنجاح');
    }

    public function edit(ServiceType $serviceType): View
    {
        return view('admin.service-types.edit', compact('serviceType'));
    }

    public function update(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $data = $this->validateData($request);

        $serviceType->update($data);

        return redirect()
            ->route('admin.service-types.index')
            ->with('success', 'تم تحديث نوع الخدمة بنجاح');
    }

    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        if ($serviceType->services()->exists()) {
            return redirect()
                ->route('admin.service-types.index')
                ->with('error', 'لا يمكن حذف نوع الخدمة لأنه مرتبط بخدمات قائمة');
        }

        $serviceType->delete();

        return redirect()
            ->route('admin.service-types.index')
            ->with('success', 'تم حذف نوع الخدمة');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
