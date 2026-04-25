<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceType;
use App\Domain\Catalog\Models\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Service CRUD.
 *
 * Services have NO price column — prices are entered per invoice item.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-002)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 3.3)
 */
class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Service::query()->with(['serviceType', 'unit']);

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('service_type_id')) {
            $query->where('service_type_id', $request->integer('service_type_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $services = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $serviceTypes = ServiceType::orderBy('sort_order')->get(['id', 'name']);

        return view('admin.services.index', compact('services', 'serviceTypes'));
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'serviceTypes' => ServiceType::active()->ordered()->get(['id', 'name']),
            'units'        => Unit::active()->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Service::create($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم إضافة الخدمة بنجاح');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service'      => $service,
            'serviceTypes' => ServiceType::active()->ordered()->get(['id', 'name']),
            'units'        => Unit::active()->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $this->validateData($request);

        $service->update($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم تحديث الخدمة بنجاح');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم حذف الخدمة');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request): array
    {
        $validated = $request->validate([
            'service_type_id' => ['required', 'integer', 'exists:service_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'description' => ['nullable', 'string'],
            'default_zatca_classification' => ['required', 'in:S,Z,E'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
