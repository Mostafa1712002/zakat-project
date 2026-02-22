@extends('layouts.app')

@section('title', 'إضافة مستخدم')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة مستخدم جديد</h1>
        <p>إضافة حساب مستخدم جديد للنظام</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('settings.users') }}" class="btn">← رجوع للمستخدمين</a>
    </div>
</div>

<form action="{{ route('settings.users.store') }}" method="POST">
    @csrf

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3>👤 البيانات الأساسية</h3>

                <div class="form-group">
                    <label class="form-label">الاسم *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">كلمة المرور *</label>
                        <input type="password" name="password" class="form-control" required>
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">تأكيد كلمة المرور *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-control">
                            <option value="">بدون فرع</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>🔐 الصلاحيات</h3>

                <div class="form-group">
                    <label class="form-label">الأدوار</label>
                    <div class="checkbox-grid">
                        @foreach($roles as $role)
                            @php
                                $roleLabels = [
                                    'admin' => 'مدير',
                                    'branch_manager' => 'مدير فرع',
                                    'accountant' => 'محاسب',
                                    'sales_rep' => 'مندوب مبيعات',
                                    'employee' => 'موظف',
                                ];
                            @endphp
                            <label class="checkbox-item">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                       {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}
                                       onchange="toggleEmployeeSection()">
                                <span>{{ $roleLabels[$role->name] ?? $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- قسم ربط الموظف -->
                <div class="form-group" id="employeeSection" style="display: none; margin-top: 1rem;">
                    <label class="form-label">ربط بموظف</label>
                    <select name="employee_id" class="form-control">
                        <option value="">-- اختر موظف (اختياري) --</option>
                        @foreach($availableEmployees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} - {{ $employee->job_title ?? 'بدون وظيفة' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">ربط المستخدم بسجل موظف موجود لتفعيل صلاحيات الموظف</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 حفظ المستخدم</button>
            <a href="{{ route('settings.users') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.checkbox-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; }
.checkbox-item { display: flex; align-items: center; gap: 0.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
.text-muted { color: #6b7280; font-size: 12px; display: block; margin-top: 4px; }

@media (max-width: 768px) {
    .grid-2 { grid-template-columns: 1fr; gap: 1rem; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<script>
function toggleEmployeeSection() {
    const employeeCheckbox = document.querySelector('input[name="roles[]"][value="employee"]');
    const employeeSection = document.getElementById('employeeSection');

    if (employeeCheckbox && employeeCheckbox.checked) {
        employeeSection.style.display = 'block';
    } else {
        employeeSection.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', toggleEmployeeSection);
</script>
@endsection
