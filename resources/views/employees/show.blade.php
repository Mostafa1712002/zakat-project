@extends('layouts.app')

@section('title', 'عرض الموظف')

@section('content')
<div class="page-header">
    <div>
        <h1>👨‍💼 {{ $employee->name }}</h1>
        <p>{{ $employee->employee_code }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employees.edit', $employee) }}" class="btn">تعديل</a>
        <a href="{{ route('employees.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body  overflow-auto">
            <h3>👤 البيانات الشخصية</h3>
            <table class="info-table text-nowrap">
                <tr><td>الاسم</td><td><strong>{{ $employee->name }}</strong></td></tr>
                <tr><td>كود الموظف</td><td><code>{{ $employee->employee_code }}</code></td></tr>
                <tr><td>الرقم القومي</td><td>{{ $employee->national_id ?? '-' }}</td></tr>
                <tr><td>البريد الإلكتروني</td><td>{{ $employee->email ?? '-' }}</td></tr>
                <tr><td>الهاتف</td><td>{{ $employee->phone ?? '-' }}</td></tr>
                <tr><td>العنوان</td><td>{{ $employee->address ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-body  overflow-auto">
            <h3>💼 البيانات الوظيفية</h3>
            <table class="info-table">
                <tr><td>المسمى الوظيفي</td><td>{{ $employee->job_title ?? '-' }}</td></tr>
                <tr><td>القسم</td><td>{{ $employee->department ?? '-' }}</td></tr>
                <tr><td>الفرع</td><td>{{ $employee->branch->name ?? '-' }}</td></tr>
                <tr><td>تاريخ التعيين</td><td>{{ $employee->hire_date?->format('Y-m-d') ?? '-' }}</td></tr>
                <tr><td>تاريخ إنهاء الخدمة</td><td>{{ $employee->termination_date?->format('Y-m-d') ?? '-' }}</td></tr>
                <tr><td>الراتب</td><td>{{ $employee->salary ? number_format($employee->salary, 2) . ' ج.م' : '-' }}</td></tr>
                <tr>
                    <td>الحالة</td>
                    <td>
                        @if($employee->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                </tr>
                <tr><td>سنوات الخدمة</td><td>{{ $employee->years_of_service }} سنة</td></tr>
            </table>
        </div>
    </div>
</div>

@if($employee->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>📝 ملاحظات</h3>
        <p>{{ $employee->notes }}</p>
    </div>
</div>
@endif

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
.info-table { width: 100%; }
.info-table tr td { padding: 0.75rem 0; border-bottom: 1px solid var(--border-color); }
.info-table tr td:first-child { color: #6b7280; width: 40%; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-success { background: #10b981; color: white; }
.badge-danger { background: #ef4444; color: white; }
</style>
@endsection
