@extends('layouts.app')

@section('title', 'إضافة ميزة')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة ميزة جديدة</h1>
        <p>أضف ميزة جديدة للتحكم في وظائف النظام</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('features.index') }}" class="btn">← رجوع للمميزات</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('features.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="name" class="form-label">المعرف الفريد (بالإنجليزية) *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="مثال: invoices">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <small style="color: #64748b;">يستخدم للتحقق من حالة الميزة برمجياً</small>
                </div>

                <div class="form-group">
                    <label for="name_ar" class="form-label">اسم الميزة (بالعربية) *</label>
                    <input type="text" name="name_ar" id="name_ar" class="form-control" value="{{ old('name_ar') }}" required placeholder="مثال: الفواتير">
                    @error('name_ar')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="description" class="form-label">الوصف (بالإنجليزية)</label>
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="وصف مختصر للميزة...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description_ar" class="form-label">الوصف (بالعربية)</label>
                    <textarea name="description_ar" id="description_ar" class="form-control" rows="3" placeholder="وصف مختصر للميزة بالعربية...">{{ old('description_ar') }}</textarea>
                    @error('description_ar')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="icon" class="form-label">الأيقونة</label>
                    <input type="text" name="icon" id="icon" class="form-control" value="{{ old('icon', '⚙️') }}" placeholder="مثال: 📄">
                    @error('icon')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <small style="color: #64748b;">استخدم رمز إيموجي</small>
                </div>

                <div class="form-group">
                    <label for="group" class="form-label">المجموعة *</label>
                    <input type="text" name="group" id="group" class="form-control" value="{{ old('group', 'general') }}" list="groups-list" required>
                    <datalist id="groups-list">
                        <option value="general">عام</option>
                        <option value="sales">المبيعات</option>
                        <option value="inventory">المخزون</option>
                        <option value="reports">التقارير</option>
                        <option value="settings">الإعدادات</option>
                        @foreach($groups as $group)
                            @if(!in_array($group, ['general', 'sales', 'inventory', 'reports', 'settings']))
                            <option value="{{ $group }}">{{ $group }}</option>
                            @endif
                        @endforeach
                    </datalist>
                    @error('group')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sort_order" class="form-label">ترتيب العرض</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                    @error('sort_order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="route_name" class="form-label">اسم المسار</label>
                <input type="text" name="route_name" id="route_name" class="form-control" value="{{ old('route_name') }}" placeholder="مثال: invoices.index">
                @error('route_name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <small style="color: #64748b;">اسم المسار في لارافيل (اختياري)</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                    <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', true) ? 'checked' : '' }} style="width: 20px; height: 20px;">
                    <span>تفعيل الميزة</span>
                </label>
                <small style="color: #64748b;">عند تفعيل الميزة ستكون متاحة لجميع المستخدمين</small>
            </div>

            <div class="form-group" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
                <button type="submit" class="btn btn-primary">💾 حفظ الميزة</button>
                <a href="{{ route('features.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
