@extends('layouts.app')

@section('title', 'عرض الموظف')

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $employee->name }}</h1>
        <p>{{ $employee->employee_code }} - {{ $employee->job_title ?? 'موظف' }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employee-transactions.employee-history', $employee) }}" class="btn btn-primary">المعاملات المالية</a>
        <a href="{{ route('employee-transactions.create', ['employee_id' => $employee->id]) }}" class="btn btn-success">+ معاملة جديدة</a>
        <a href="{{ route('employees.edit', $employee) }}" class="btn">تعديل</a>
        <a href="{{ route('employees.index') }}" class="btn">رجوع</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-icon primary">💼</div>
        <div class="stat-value">{{ number_format($employee->salary ?? 0) }} ج.م</div>
        <div class="stat-label">الراتب الأساسي</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">💰</div>
        <div class="stat-value">{{ number_format($stats['total_salaries']) }} ج.م</div>
        <div class="stat-label">إجمالي المرتبات</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">💳</div>
        <div class="stat-value">{{ number_format($stats['total_advances']) }} ج.م</div>
        <div class="stat-label">إجمالي السلف</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $stats['balance'] >= 0 ? 'success' : 'danger' }}">📊</div>
        <div class="stat-value">{{ number_format(abs($stats['balance'])) }} ج.م</div>
        <div class="stat-label">{{ $stats['balance'] >= 0 ? 'الرصيد' : 'عليه' }}</div>
    </div>
</div>

<div class="grid-3">
    <!-- Personal Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">البيانات الشخصية</h3>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr><td>الاسم</td><td><strong>{{ $employee->name }}</strong></td></tr>
                <tr><td>كود الموظف</td><td><code>{{ $employee->employee_code }}</code></td></tr>
                <tr><td>الهاتف</td><td>{{ $employee->phone ?? '-' }}</td></tr>
                <tr><td>البريد الإلكتروني</td><td>{{ $employee->email ?? '-' }}</td></tr>
                <tr><td>العنوان</td><td>{{ $employee->address ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <!-- Job Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">البيانات الوظيفية</h3>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr><td>المسمى الوظيفي</td><td>{{ $employee->job_title ?? '-' }}</td></tr>
                <tr><td>القسم</td><td>{{ $employee->department ?? '-' }}</td></tr>
                <tr><td>الفرع</td><td>{{ $employee->branch->name ?? '-' }}</td></tr>
                <tr><td>تاريخ التعيين</td><td>{{ $employee->hire_date?->format('Y-m-d') ?? '-' }}</td></tr>
                <tr><td>مدة الخدمة</td><td>{{ number_format($stats['years_of_service'], 1) }} سنة</td></tr>
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
                @if($employee->termination_date)
                <tr><td>تاريخ إنهاء الخدمة</td><td>{{ $employee->termination_date->format('Y-m-d') }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <!-- Account Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">حساب تسجيل الدخول</h3>
        </div>
        <div class="card-body">
            @if($employee->user_id)
                <div class="account-status success">
                    <span class="status-icon">✓</span>
                    <span>لديه حساب تسجيل دخول</span>
                </div>
                <table class="info-table">
                    <tr><td>البريد الإلكتروني</td><td>{{ $employee->user->email }}</td></tr>
                    <tr><td>اسم المستخدم</td><td>{{ $employee->user->name }}</td></tr>
                    <tr>
                        <td>حالة الحساب</td>
                        <td>
                            @if($employee->user->is_active)
                                <span class="badge badge-success">نشط</span>
                            @else
                                <span class="badge badge-danger">معطل</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>الأدوار</td>
                        <td>
                            @foreach($employee->user->roles as $role)
                                <span class="badge badge-primary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                </table>
            @else
                <div class="account-status warning">
                    <span class="status-icon">!</span>
                    <span>لا يوجد حساب تسجيل دخول</span>
                </div>
                <p style="color: var(--text-muted); font-size: 14px; margin-top: 12px;">
                    يمكنك إنشاء حساب تسجيل دخول من صفحة التعديل
                </p>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-sm" style="margin-top: 12px;">
                    إنشاء حساب
                </a>
            @endif
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

<!-- Recent Transactions -->
@if($employee->transactions && $employee->transactions->count() > 0)
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3 class="card-title">آخر المعاملات</h3>
        <a href="{{ route('employee-transactions.employee-history', $employee) }}" class="btn btn-sm">عرض الكل</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>رقم المعاملة</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                    <th>الوصف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_number }}</td>
                    <td>
                        @php
                            $typeColors = [
                                'salary' => 'success',
                                'advance' => 'warning',
                                'bonus' => 'primary',
                                'deduction' => 'danger',
                            ];
                        @endphp
                        <span class="badge badge-{{ $typeColors[$transaction->type] ?? 'secondary' }}">
                            {{ $transaction->type_name }}
                        </span>
                    </td>
                    <td style="font-weight: 600; color: {{ $transaction->isCredit() ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $transaction->isCredit() ? '+' : '-' }}{{ number_format($transaction->amount) }} ج.م
                    </td>
                    <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                    <td>{{ $transaction->description ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    text-align: center;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 24px;
}

.stat-icon.primary { background: rgba(8, 145, 178, 0.1); }
.stat-icon.success { background: rgba(16, 185, 129, 0.1); }
.stat-icon.warning { background: rgba(245, 158, 11, 0.1); }
.stat-icon.danger { background: rgba(239, 68, 68, 0.1); }

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 4px;
}

.stat-label {
    font-size: 13px;
    color: var(--text-muted);
}

.grid-3 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.info-table {
    width: 100%;
}

.info-table tr td {
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}

.info-table tr:last-child td {
    border-bottom: none;
}

.info-table tr td:first-child {
    color: var(--text-muted);
    font-size: 14px;
    width: 45%;
}

.account-status {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    margin-bottom: 16px;
}

.account-status.success {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.account-status.warning {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.status-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.account-status.success .status-icon {
    background: #059669;
    color: white;
}

.account-status.warning .status-icon {
    background: #d97706;
    color: white;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success { background: rgba(16, 185, 129, 0.1); color: #059669; }
.badge-warning { background: rgba(245, 158, 11, 0.1); color: #d97706; }
.badge-danger { background: rgba(239, 68, 68, 0.1); color: #dc2626; }
.badge-primary { background: rgba(8, 145, 178, 0.1); color: #0891b2; }
.badge-secondary { background: rgba(100, 116, 139, 0.1); color: #64748b; }

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
