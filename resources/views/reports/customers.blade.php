@extends('layouts.app')

@section('title', 'تقرير العملاء')

@section('content')
<div class="page-header">
    <div>
        <h1>👥 تقرير العملاء</h1>
        <p>تحليل أفضل العملاء والأرصدة المستحقة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('reports.index') }}" class="btn">← التقارير</a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="form-group">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="form-group">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">🔍 عرض التقرير</button>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
            <div class="stat-value">{{ $totalCustomers }}</div>
            <div class="stat-label">إجمالي العملاء</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <div class="stat-value">{{ $activeCustomers }}</div>
            <div class="stat-label">عملاء نشطون</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totalSalesAmount, 2) }} ج.م</div>
            <div class="stat-label">إجمالي المبيعات</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($averageSalePerCustomer, 2) }} ج.م</div>
            <div class="stat-label">متوسط المبيعات للعميل</div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>🏆 أفضل العملاء</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>عدد الفواتير</th>
                    <th>إجمالي المبيعات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->sales_count }}</td>
                    <td>{{ number_format($customer->sales_sum_total_amount ?? 0, 2) }} ج.م</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>لا توجد بيانات</h3>
                            <p>لم يتم تسجيل مبيعات خلال هذه الفترة</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>💳 الأرصدة المستحقة</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الرصيد المستحق</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customersWithBalance as $index => $customer)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $customer->name }}</td>
                    <td class="text-danger">{{ number_format($customer->current_balance, 2) }} ج.م</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">
                        <div class="empty-state">
                            <div class="empty-state-icon">💳</div>
                            <h3>لا توجد أرصدة مستحقة</h3>
                            <p>جميع العملاء قاموا بالسداد</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($customersWithBalance->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="2"><strong>الإجمالي</strong></td>
                    <td><strong>{{ number_format($totalOutstanding, 2) }} ج.م</strong></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--card); border-radius: var(--radius); padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow); }
.stat-icon { font-size: 2.5rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-label { font-size: 0.875rem; color: var(--text-muted); }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); }
.card-header h3 { margin: 0; font-size: 1.125rem; }
</style>
@endsection
