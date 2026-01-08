@extends('layouts.app')

@section('title', 'تقرير الأرباح')

@section('content')
<div class="page-header">
    <div>
        <h1>📊 تقرير الأرباح</h1>
        <p>تحليل الإيرادات والتكلفة وصافي الربح</p>
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
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totalRevenue, 2) }} ج.م</div>
            <div class="stat-label">إجمالي الإيرادات</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totalCost, 2) }} ج.م</div>
            <div class="stat-label">إجمالي التكلفة</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
            <div class="stat-value {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totalProfit, 2) }} ج.م</div>
            <div class="stat-label">صافي الربح</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💹</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($profitMargin, 1) }}%</div>
            <div class="stat-label">هامش الربح</div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>📈 الربح حسب التاريخ</h3>
    </div>
    <div class="card-body">
        @if($profitsByDate->count() > 0)
        <div class="chart-container" style="height: 300px;">
            <canvas id="profitsChart"></canvas>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <h3>لا توجد بيانات</h3>
            <p>لم يتم تسجيل مبيعات خلال الفترة المحددة</p>
        </div>
        @endif
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>📋 تفاصيل الأرباح اليومية</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الإيرادات</th>
                    <th>التكلفة</th>
                    <th>صافي الربح</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profitsByDate as $date => $row)
                <tr>
                    <td>{{ $date }}</td>
                    <td>{{ number_format($row['revenue'], 2) }} ج.م</td>
                    <td>{{ number_format($row['cost'], 2) }} ج.م</td>
                    <td class="{{ $row['profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($row['profit'], 2) }} ج.م</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="empty-state-icon">📊</div>
                            <h3>لا توجد بيانات</h3>
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
        <h3>🏆 الأصناف الأكثر ربحاً</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الكمية</th>
                    <th>إجمالي الربح</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profitableProducts as $item)
                <tr>
                    <td>{{ $item->product->name ?? '-' }}</td>
                    <td>{{ number_format($item->total_quantity, 2) }}</td>
                    <td class="text-success">{{ number_format($item->total_profit, 2) }} ج.م</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <h3>لا توجد بيانات</h3>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($profitsByDate->count() > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = {!! json_encode($profitsByDate->keys()->values()) !!};
    const revenue = {!! json_encode($profitsByDate->pluck('revenue')->values()) !!};
    const cost = {!! json_encode($profitsByDate->pluck('cost')->values()) !!};
    const profit = {!! json_encode($profitsByDate->pluck('profit')->values()) !!};

    new Chart(document.getElementById('profitsChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'الإيرادات', data: revenue, backgroundColor: 'rgba(34, 197, 94, 0.5)', borderColor: 'rgb(34, 197, 94)', borderWidth: 1 },
                { label: 'التكلفة', data: cost, backgroundColor: 'rgba(239, 68, 68, 0.5)', borderColor: 'rgb(239, 68, 68)', borderWidth: 1 },
                { label: 'الربح', data: profit, backgroundColor: 'rgba(59, 130, 246, 0.5)', borderColor: 'rgb(59, 130, 246)', borderWidth: 1 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return value.toLocaleString('ar-SA') + ' ج.م'; }
                    }
                }
            }
        }
    });
</script>
@endpush
@endif

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
