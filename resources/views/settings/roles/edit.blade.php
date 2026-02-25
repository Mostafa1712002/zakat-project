@extends('layouts.app')

@section('title', 'تعديل الدور')

@section('content')
<div class="page-header">
    <div>
        <h1>تعديل الدور: {{ $role->name }}</h1>
        <p>تعديل بيانات الدور وصلاحياته</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('settings.roles') }}" class="btn">← رجوع للأدوار</a>
    </div>
</div>

<form action="{{ route('settings.roles.update', $role->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">بيانات الدور</h3>

            <div class="form-group">
                <label for="name" class="form-label">اسم الدور (بالإنجليزية) *</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $role->name) }}" required placeholder="مثال: accountant">
                <small class="form-hint">اسم فريد بدون مسافات (يُستخدم في النظام)</small>
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">الصلاحيات</h3>
            <p class="text-muted" style="margin-bottom: 20px;">اختر الصلاحيات التي سيحصل عليها المستخدمون بهذا الدور</p>

            <div class="permissions-grid">
                @php
                    $permissionLabels = [
                        'sales' => 'المبيعات',
                        'purchases' => 'المشتريات',
                        'products' => 'الأصناف',
                        'warehouses' => 'المخازن',
                        'inventory' => 'المخزون',
                        'transfers' => 'التحويلات',
                        'customers' => 'العملاء',
                        'suppliers' => 'الموردين',
                    ];

                    $actionLabels = [
                        'view' => 'عرض',
                        'create' => 'إنشاء',
                        'edit' => 'تعديل',
                        'delete' => 'حذف',
                    ];
                @endphp

                @foreach($permissions as $group => $groupPermissions)
                <div class="permission-group">
                    <div class="permission-group-header">
                        <label class="checkbox-label">
                            <input type="checkbox" class="group-checkbox" data-group="{{ $group }}">
                            <span>{{ $permissionLabels[$group] ?? ucfirst($group) }}</span>
                        </label>
                    </div>
                    <div class="permission-group-body">
                        @foreach($groupPermissions as $permission)
                        @php
                            $action = explode('_', $permission->name)[0];
                        @endphp
                        <label class="checkbox-label permission-item">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="permission-checkbox" data-group="{{ $group }}" {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                            <span>{{ $actionLabels[$action] ?? $action }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">حفظ التغييرات</button>
            <a href="{{ route('settings.roles') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.permission-group {
    border: 1px solid var(--border-color);
    border-radius: 10px;
    overflow: hidden;
}

.permission-group-header {
    background: var(--bg-light);
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
}

.permission-group-body {
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.permission-item {
    padding: 6px 8px;
    border-radius: 6px;
    transition: background 0.2s;
}

.permission-item:hover {
    background: var(--bg-light);
}

.form-hint {
    display: block;
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
}

.btn-lg {
    padding: 14px 32px;
    font-size: 1rem;
}

.text-muted {
    color: var(--text-muted);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Group checkbox functionality
    document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
        groupCheckbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const isChecked = this.checked;
            document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`).forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    });

    // Update group checkbox when individual permissions change
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
            const checkedCount = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]:checked`).length;
            const groupCheckbox = document.querySelector(`.group-checkbox[data-group="${group}"]`);

            if (groupCheckbox) {
                groupCheckbox.checked = checkedCount === groupCheckboxes.length;
                groupCheckbox.indeterminate = checkedCount > 0 && checkedCount < groupCheckboxes.length;
            }
        });
    });

    // Initialize group checkbox states
    document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
        const group = groupCheckbox.dataset.group;
        const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
        const checkedCount = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]:checked`).length;

        groupCheckbox.checked = checkedCount === groupCheckboxes.length;
        groupCheckbox.indeterminate = checkedCount > 0 && checkedCount < groupCheckboxes.length;
    });
});
</script>
@endsection
