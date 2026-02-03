@extends('layouts.app')

@section('title', 'لوحة تحكم الموظف')

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $employee->name }}</h1>
        <p>{{ $employee->job_title ?? 'موظف' }} - {{ $employee->employee_code }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employee.statement') }}" class="btn">كشف الحساب</a>
        <a href="{{ route('employee.profile') }}" class="btn">الملف الشخصي</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">💼</div>
        <div class="stat-value">{{ number_format($stats['salary']) }} ج.م</div>
        <div class="stat-label">الراتب الأساسي</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">💰</div>
        <div class="stat-value">{{ number_format($stats['total_salaries_paid']) }} ج.م</div>
        <div class="stat-label">إجمالي المرتبات المستلمة</div>
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

<!-- Additional Info -->
<div class="grid-2" style="margin-bottom: 20px;">
    <!-- Employee Info Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">معلومات وظيفية</h3>
        </div>
        <div class="card-body">
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">القسم</span>
                    <span class="info-value">{{ $employee->department ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">الفرع</span>
                    <span class="info-value">{{ $employee->branch?->name ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">تاريخ التعيين</span>
                    <span class="info-value">{{ $employee->hire_date?->format('Y-m-d') ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">مدة الخدمة</span>
                    <span class="info-value">{{ number_format($stats['years_of_service'], 1) }} سنة</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Summary Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ملخص المعاملات</h3>
        </div>
        <div class="card-body">
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">عدد المرتبات</span>
                    <span class="info-value badge badge-success">{{ $transactionsByType['salaries'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">عدد السلف</span>
                    <span class="info-value badge badge-warning">{{ $transactionsByType['advances'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">عدد المكافآت</span>
                    <span class="info-value badge badge-primary">{{ $transactionsByType['bonuses'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">عدد الخصومات</span>
                    <span class="info-value badge badge-danger">{{ $transactionsByType['deductions'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Stats -->
<div class="grid-2" style="margin-bottom: 20px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">إحصائيات الشهر الحالي</h3>
        </div>
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: var(--success);">
                {{ number_format($stats['monthly_salary']) }} ج.م
            </div>
            <div style="font-size: 14px; color: var(--text-muted); margin-top: 8px;">
                المرتبات المستلمة هذا الشهر
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">إحصائيات السنة الحالية</h3>
        </div>
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: var(--primary);">
                {{ number_format($stats['yearly_salary']) }} ج.م
            </div>
            <div style="font-size: 14px; color: var(--text-muted); margin-top: 8px;">
                إجمالي المرتبات هذا العام
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">آخر المعاملات</h3>
        <a href="{{ route('employee.statement') }}" class="btn btn-sm">عرض الكل</a>
    </div>
    <div class="card-body overflow-auto">
        @if($recentTransactions->count() > 0)
        <table class="table text-nowrap">
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
                @foreach($recentTransactions as $transaction)
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
        @else
        <div class="empty-state">
            <p>لا توجد معاملات مسجلة</p>
        </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
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
        font-size: 24px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 14px;
        color: var(--text-muted);
    }

    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .info-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--text-muted);
        font-size: 14px;
    }

    .info-value {
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
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

    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush
