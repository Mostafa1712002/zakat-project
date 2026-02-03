@extends('layouts.app')

@section('title', 'تقارير الموظف')

@section('content')
<div class="page-header">
    <div>
        <h1>التقارير المالية</h1>
        <p>{{ $employee->name }} - {{ $employee->employee_code }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employee.dashboard') }}" class="btn">لوحة التحكم</a>
    </div>
</div>

<!-- Year Selector -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" action="{{ route('employee.reports') }}" style="display: flex; align-items: center; gap: 16px;">
            <label class="form-label" style="margin: 0;">السنة:</label>
            <select name="year" class="form-control" style="width: auto;" onchange="this.form.submit()">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>
</div>

<!-- Yearly Summary -->
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-icon success">💰</div>
        <div class="stat-value">{{ number_format($yearlyTotals['salary']) }} ج.م</div>
        <div class="stat-label">إجمالي المرتبات</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">💳</div>
        <div class="stat-value">{{ number_format($yearlyTotals['advances']) }} ج.م</div>
        <div class="stat-label">إجمالي السلف</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">🎁</div>
        <div class="stat-value">{{ number_format($yearlyTotals['bonuses']) }} ج.م</div>
        <div class="stat-label">إجمالي المكافآت</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">📉</div>
        <div class="stat-value">{{ number_format($yearlyTotals['deductions']) }} ج.م</div>
        <div class="stat-label">إجمالي الخصومات</div>
    </div>
</div>

<!-- Net Summary -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="text-align: center;">
        @php
            $netIncome = $yearlyTotals['salary'] + $yearlyTotals['bonuses'] - $yearlyTotals['deductions'];
        @endphp
        <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">صافي الدخل لعام {{ $year }}</div>
        <div style="font-size: 36px; font-weight: bold; color: var(--success);">
            {{ number_format($netIncome) }} ج.م
        </div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">
            (المرتبات + المكافآت - الخصومات)
        </div>
    </div>
</div>

<!-- Monthly Report Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">التقرير الشهري لعام {{ $year }}</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive overflow-auto">
            <table class="table text-nowrap">
                <thead>
                    <tr>
                        <th>الشهر</th>
                        <th>المرتب</th>
                        <th>السلف</th>
                        <th>المكافآت</th>
                        <th>الخصومات</th>
                        <th>الصافي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyData as $month)
                    @php
                        $monthNet = $month['salary'] + $month['bonuses'] - $month['deductions'];
                        $hasData = $month['salary'] > 0 || $month['advances'] > 0 || $month['bonuses'] > 0 || $month['deductions'] > 0;
                    @endphp
                    <tr class="{{ !$hasData ? 'no-data' : '' }}">
                        <td style="font-weight: 600;">{{ $month['month_name'] }}</td>
                        <td style="color: var(--success);">{{ $month['salary'] > 0 ? number_format($month['salary']) . ' ج.م' : '-' }}</td>
                        <td style="color: var(--warning);">{{ $month['advances'] > 0 ? number_format($month['advances']) . ' ج.م' : '-' }}</td>
                        <td style="color: var(--primary);">{{ $month['bonuses'] > 0 ? number_format($month['bonuses']) . ' ج.م' : '-' }}</td>
                        <td style="color: var(--danger);">{{ $month['deductions'] > 0 ? number_format($month['deductions']) . ' ج.م' : '-' }}</td>
                        <td style="font-weight: 600; color: {{ $monthNet >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                            {{ $hasData ? number_format($monthNet) . ' ج.م' : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: var(--bg); font-weight: 700;">
                        <td>الإجمالي</td>
                        <td style="color: var(--success);">{{ number_format($yearlyTotals['salary']) }} ج.م</td>
                        <td style="color: var(--warning);">{{ number_format($yearlyTotals['advances']) }} ج.م</td>
                        <td style="color: var(--primary);">{{ number_format($yearlyTotals['bonuses']) }} ج.م</td>
                        <td style="color: var(--danger);">{{ number_format($yearlyTotals['deductions']) }} ج.م</td>
                        <td style="color: var(--success);">{{ number_format($netIncome) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>
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

    .table-responsive {
        overflow-x: auto;
    }

    tr.no-data {
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush
