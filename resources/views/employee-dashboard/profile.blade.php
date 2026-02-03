@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
<div class="page-header">
    <div>
        <h1>الملف الشخصي</h1>
        <p>{{ $employee->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employee.dashboard') }}" class="btn">لوحة التحكم</a>
    </div>
</div>

<div class="grid-2">
    <!-- Personal Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">البيانات الشخصية</h3>
        </div>
        <div class="card-body">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    {{ mb_substr($employee->name, 0, 1) }}
                </div>
                <div class="avatar-info">
                    <h2>{{ $employee->name }}</h2>
                    <p>{{ $employee->job_title ?? 'موظف' }}</p>
                </div>
            </div>

            <div class="info-list">
                <div class="info-item">
                    <span class="info-icon">📧</span>
                    <div>
                        <span class="info-label">البريد الإلكتروني</span>
                        <span class="info-value">{{ $employee->email ?? $employee->user?->email ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">📱</span>
                    <div>
                        <span class="info-label">الهاتف</span>
                        <span class="info-value">{{ $employee->phone ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">📍</span>
                    <div>
                        <span class="info-label">العنوان</span>
                        <span class="info-value">{{ $employee->address ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">🆔</span>
                    <div>
                        <span class="info-label">الرقم القومي</span>
                        <span class="info-value">{{ $employee->national_id ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">البيانات الوظيفية</h3>
        </div>
        <div class="card-body">
            <div class="info-list">
                <div class="info-item">
                    <span class="info-icon">🏢</span>
                    <div>
                        <span class="info-label">كود الموظف</span>
                        <span class="info-value">{{ $employee->employee_code }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">💼</span>
                    <div>
                        <span class="info-label">المسمى الوظيفي</span>
                        <span class="info-value">{{ $employee->job_title ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">🏬</span>
                    <div>
                        <span class="info-label">القسم</span>
                        <span class="info-value">{{ $employee->department ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">🏪</span>
                    <div>
                        <span class="info-label">الفرع</span>
                        <span class="info-value">{{ $employee->branch?->name ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">📅</span>
                    <div>
                        <span class="info-label">تاريخ التعيين</span>
                        <span class="info-value">{{ $employee->hire_date?->format('Y-m-d') ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">⏳</span>
                    <div>
                        <span class="info-label">مدة الخدمة</span>
                        <span class="info-value">{{ number_format($employee->years_of_service, 1) }} سنة</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">💰</span>
                    <div>
                        <span class="info-label">الراتب الأساسي</span>
                        <span class="info-value">{{ $employee->salary ? number_format($employee->salary) . ' ج.م' : '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-icon">✅</span>
                    <div>
                        <span class="info-label">الحالة</span>
                        <span class="info-value">
                            @if($employee->is_active)
                                <span class="badge badge-success">نشط</span>
                            @else
                                <span class="badge badge-danger">غير نشط</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3 class="card-title">الملخص المالي</h3>
    </div>
    <div class="card-body">
        <div class="financial-grid">
            <div class="financial-item">
                <span class="financial-label">إجمالي المرتبات المستلمة</span>
                <span class="financial-value success">{{ number_format($employee->total_salaries_paid) }} ج.م</span>
            </div>
            <div class="financial-item">
                <span class="financial-label">إجمالي السلف</span>
                <span class="financial-value warning">{{ number_format($employee->total_advances) }} ج.م</span>
            </div>
            <div class="financial-item">
                <span class="financial-label">الرصيد الحالي</span>
                <span class="financial-value {{ $employee->balance >= 0 ? 'success' : 'danger' }}">
                    {{ number_format(abs($employee->balance)) }} ج.م
                    {{ $employee->balance < 0 ? '(عليه)' : '' }}
                </span>
            </div>
        </div>
    </div>
</div>

@if($employee->notes)
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3 class="card-title">ملاحظات</h3>
    </div>
    <div class="card-body">
        <p>{{ $employee->notes }}</p>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
    }

    .profile-avatar {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border);
    }

    .avatar-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark, #0e7490));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
    }

    .avatar-info h2 {
        margin: 0 0 4px 0;
        font-size: 20px;
    }

    .avatar-info p {
        margin: 0;
        color: var(--text-muted);
        font-size: 14px;
    }

    .info-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .info-icon {
        font-size: 20px;
        width: 32px;
        text-align: center;
    }

    .info-item > div {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .info-label {
        font-size: 12px;
        color: var(--text-muted);
    }

    .info-value {
        font-size: 14px;
        font-weight: 500;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-success { background: rgba(16, 185, 129, 0.1); color: #059669; }
    .badge-danger { background: rgba(239, 68, 68, 0.1); color: #dc2626; }

    .financial-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
    }

    .financial-item {
        text-align: center;
        padding: 20px;
        background: var(--bg);
        border-radius: 12px;
    }

    .financial-label {
        display: block;
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .financial-value {
        display: block;
        font-size: 24px;
        font-weight: 700;
    }

    .financial-value.success { color: var(--success); }
    .financial-value.warning { color: var(--warning); }
    .financial-value.danger { color: var(--danger); }

    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
