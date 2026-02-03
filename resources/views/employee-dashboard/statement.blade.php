@extends('layouts.app')

@section('title', 'كشف حساب الموظف')

@section('content')
<div class="page-header">
    <div>
        <h1>كشف الحساب</h1>
        <p>{{ $employee->name }} - {{ $employee->employee_code }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employee.dashboard') }}" class="btn">لوحة التحكم</a>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-icon success">💰</div>
        <div class="stat-value">{{ number_format($totals['salaries']) }} ج.م</div>
        <div class="stat-label">المرتبات</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">💳</div>
        <div class="stat-value">{{ number_format($totals['advances']) }} ج.م</div>
        <div class="stat-label">السلف</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">🎁</div>
        <div class="stat-value">{{ number_format($totals['bonuses']) }} ج.م</div>
        <div class="stat-label">المكافآت</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">📉</div>
        <div class="stat-value">{{ number_format($totals['deductions']) }} ج.م</div>
        <div class="stat-label">الخصومات</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" action="{{ route('employee.statement') }}" class="filter-form">
            <div class="filter-row">
                <div class="form-group">
                    <label class="form-label">النوع</label>
                    <select name="type" class="form-control">
                        <option value="">الكل</option>
                        @foreach(\App\Models\EmployeeTransaction::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">الشهر</label>
                    <input type="month" name="month_year" class="form-control" value="{{ request('month_year') }}">
                </div>

                <div class="form-group" style="align-self: end;">
                    <button type="submit" class="btn btn-primary">بحث</button>
                    <a href="{{ route('employee.statement') }}" class="btn">مسح</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">المعاملات</h3>
        <span class="badge badge-secondary">{{ $transactions->total() }} معاملة</span>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
        <div class="table-responsive overflow-auto">
            <table class="table text-nowrap">
                <thead>
                    <tr>
                        <th>رقم المعاملة</th>
                        <th>النوع</th>
                        <th>المبلغ</th>
                        <th>التاريخ</th>
                        <th>الشهر</th>
                        <th>طريقة الدفع</th>
                        <th>الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
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
                        <td>{{ $transaction->month_year ?? '-' }}</td>
                        <td>{{ $transaction->payment_method_name }}</td>
                        <td>{{ $transaction->description ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $transactions->links() }}
        </div>
        @else
        <div class="empty-state">
            <p>لا توجد معاملات مطابقة للفلتر</p>
        </div>
        @endif
    </div>
</div>

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
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        text-align: center;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 20px;
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

    .filter-form .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        align-items: end;
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

    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
    }

    .table-responsive {
        overflow-x: auto;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .filter-form .filter-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
