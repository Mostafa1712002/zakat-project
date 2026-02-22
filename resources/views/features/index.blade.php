@extends('layouts.app')

@section('title', 'إدارة المميزات')

@section('content')
<div class="page-header">
    <div>
        <h1>⚙️ إدارة المميزات</h1>
        <p>تفعيل وتعطيل مميزات النظام - متاح فقط للمسؤول الأعلى</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('features.create') }}" class="btn btn-primary">+ إضافة ميزة</a>
    </div>
</div>

{{-- إحصائيات --}}
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style=" background: linear-gradient(180deg, #0891b2 0%, #0e7490 100%); color: white; padding: 20px; border-radius: 12px; text-align: center;">
        <div style="font-size: 32px; font-weight: 700;">{{ $stats['total'] }}</div>
        <div style="font-size: 14px; opacity: 0.9;">إجمالي المميزات</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 12px; text-align: center;">
        <div style="font-size: 32px; font-weight: 700;">{{ $stats['enabled'] }}</div>
        <div style="font-size: 14px; opacity: 0.9;">مميزات مفعلة</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 12px; text-align: center;">
        <div style="font-size: 32px; font-weight: 700;">{{ $stats['disabled'] }}</div>
        <div style="font-size: 14px; opacity: 0.9;">مميزات معطلة</div>
    </div>
</div>

{{-- أزرار سريعة --}}
<div class="card" style="margin-bottom: 24px;">
    <div style="padding: 16px; display: flex; gap: 12px; flex-wrap: wrap;">
        <form action="{{ route('features.enable-all') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('هل تريد تفعيل جميع المميزات؟')">
                ✅ تفعيل الكل
            </button>
        </form>
        <form action="{{ route('features.disable-all') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-warning" onclick="return confirm('هل تريد تعطيل جميع المميزات؟')">
                ⛔ تعطيل الكل
            </button>
        </form>
    </div>
</div>

{{-- عرض المميزات حسب المجموعات --}}
@forelse($groups as $groupName => $groupFeatures)
<div class="card" style="margin-bottom: 24px;">
    <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
        <h3 style="margin: 0; font-size: 18px; color: #1e293b;">
            📁 {{ $groupName == 'general' ? 'عام' : ($groupName == 'sales' ? 'المبيعات' : ($groupName == 'inventory' ? 'المخزون' : ($groupName == 'reports' ? 'التقارير' : ($groupName == 'settings' ? 'الإعدادات' : $groupName)))) }}
            <span style="font-size: 14px; color: #64748b; font-weight: normal;">({{ $groupFeatures->count() }} ميزة)</span>
        </h3>
    </div>
    <div class="table-container  overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th style="width: 50px;">الأيقونة</th>
                    <th>اسم الميزة</th>
                    <th>الوصف</th>
                    <th>المسار</th>
                    <th style="width: 100px;">الحالة</th>
                    <th style="width: 200px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupFeatures as $feature)
                <tr style="{{ !$feature->is_enabled ? 'opacity: 0.6; background: #fef2f2;' : '' }}">
                    <td style="font-size: 24px; text-align: center;">{{ $feature->icon }}</td>
                    <td>
                        <strong>{{ $feature->name_ar }}</strong>
                        <div style="font-size: 12px; color: #64748b;">{{ $feature->name }}</div>
                    </td>
                    <td class="text-muted">{{ $feature->description_ar ?? '-' }}</td>
                    <td>
                        @if($feature->route_name)
                        <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{ $feature->route_name }}</code>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if($feature->is_enabled)
                        <span class="badge" style="background: #22c55e; color: white; padding: 4px 12px; border-radius: 20px;">✅ مفعل</span>
                        @else
                        <span class="badge" style="background: #ef4444; color: white; padding: 4px 12px; border-radius: 20px;">⛔ معطل</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <form action="{{ route('features.toggle', $feature) }}" method="POST" style="display: inline;">
                                @csrf
                                @if($feature->is_enabled)
                                <button type="submit" class="btn btn-sm btn-warning" title="تعطيل">⛔</button>
                                @else
                                <button type="submit" class="btn btn-sm btn-success" title="تفعيل">✅</button>
                                @endif
                            </form>
                            <a href="{{ route('features.edit', $feature) }}" class="btn btn-sm" title="تعديل">✏️</a>
                            <form action="{{ route('features.destroy', $feature) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الميزة؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="card">
    <div class="empty-state" style="padding: 60px 20px; text-align: center;">
        <div class="empty-state-icon" style="font-size: 64px; margin-bottom: 16px;">⚙️</div>
        <h3>لا توجد مميزات</h3>
        <p>ابدأ بإضافة ميزة جديدة للتحكم في وظائف النظام</p>
        <a href="{{ route('features.create') }}" class="btn btn-primary">+ إضافة ميزة</a>
    </div>
</div>
@endforelse

<style>
.btn-success {
    background: #22c55e;
    color: white;
}
.btn-success:hover {
    background: #16a34a;
}
.btn-warning {
    background: #f59e0b;
    color: white;
}
.btn-warning:hover {
    background: #d97706;
}
</style>
@endsection
