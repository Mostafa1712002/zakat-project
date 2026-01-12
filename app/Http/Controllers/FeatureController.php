<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    /**
     * عرض قائمة المميزات
     */
    public function index()
    {
        $features = Feature::orderBy('group')->orderBy('sort_order')->get();
        $groups = $features->groupBy('group');

        $stats = [
            'total' => $features->count(),
            'enabled' => $features->where('is_enabled', true)->count(),
            'disabled' => $features->where('is_enabled', false)->count(),
        ];

        return view('features.index', compact('features', 'groups', 'stats'));
    }

    /**
     * عرض نموذج إضافة ميزة جديدة
     */
    public function create()
    {
        $groups = Feature::distinct()->pluck('group')->toArray();
        return view('features.create', compact('groups'));
    }

    /**
     * حفظ ميزة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:features',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'description_ar' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:10',
            'route_name' => 'nullable|string|max:255',
            'group' => 'required|string|max:100',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_enabled'] = $request->has('is_enabled');
        $validated['icon'] = $validated['icon'] ?? '⚙️';

        Feature::create($validated);

        return redirect()->route('features.index')
            ->with('success', 'تم إضافة الميزة بنجاح');
    }

    /**
     * عرض نموذج تعديل ميزة
     */
    public function edit(Feature $feature)
    {
        $groups = Feature::distinct()->pluck('group')->toArray();
        return view('features.edit', compact('feature', 'groups'));
    }

    /**
     * تحديث ميزة
     */
    public function update(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:features,name,' . $feature->id,
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'description_ar' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:10',
            'route_name' => 'nullable|string|max:255',
            'group' => 'required|string|max:100',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_enabled'] = $request->has('is_enabled');

        $feature->update($validated);
        Feature::clearAllCache();

        return redirect()->route('features.index')
            ->with('success', 'تم تحديث الميزة بنجاح');
    }

    /**
     * حذف ميزة
     */
    public function destroy(Feature $feature)
    {
        $feature->delete();
        Feature::clearAllCache();

        return redirect()->route('features.index')
            ->with('success', 'تم حذف الميزة بنجاح');
    }

    /**
     * تبديل حالة الميزة (تفعيل/تعطيل)
     */
    public function toggle(Feature $feature)
    {
        if ($feature->is_enabled) {
            $feature->disable();
            $message = "تم تعطيل ميزة {$feature->name_ar}";
        } else {
            $feature->enable();
            $message = "تم تفعيل ميزة {$feature->name_ar}";
        }

        return redirect()->route('features.index')
            ->with('success', $message);
    }

    /**
     * تفعيل جميع المميزات
     */
    public function enableAll()
    {
        Feature::query()->update(['is_enabled' => true]);
        Feature::clearAllCache();

        return redirect()->route('features.index')
            ->with('success', 'تم تفعيل جميع المميزات');
    }

    /**
     * تعطيل جميع المميزات
     */
    public function disableAll()
    {
        Feature::query()->update(['is_enabled' => false]);
        Feature::clearAllCache();

        return redirect()->route('features.index')
            ->with('success', 'تم تعطيل جميع المميزات');
    }
}
