@extends('layouts.app')

@section('title', 'تقرير المبيعات')

@section('content')
<div class="page-header">
    <div>
        <h1>📊 تقرير المبيعات</h1>
        <p>تحليل المبيعات والإيرادات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('reports.index') }}" class="btn">← التقارير</a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="form-group">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="form-group">
                <label class="form-label">العميل</label>
                <select name="customer_id" class="form-control">
                    <option value="">كل العملاء</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ (string)$customerId === (string)$customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>مؤكدة</option>
                    <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                </select>
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">🔍 عرض التقرير</button>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🧾</div>
        <div class="stat-content">
            <div class="stat-value">{{ $totalSales }}</div>
            <div class="stat-label">عدد الفواتير</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totalAmount, 2) }} ج.م</div>
            <div class="stat-label">إجمالي المبيعات</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($paidAmount, 2) }} ج.م</div>
            <div class="stat-label">المدفوع</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($remainingAmount, 2) }} ج.م</div>
            <div class="stat-label">المتبقي</div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>📈 المبيعات اليومية</h3>
    </div>
    <div class="card-body">
        @if($dailySales->count() > 0)
        <div class="chart-container" style="height: 300px;">
            <canvas id="salesChart"></canvas>
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
        <h3>📋 تفاصيل المبيعات</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th>حالة الدفع</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td><strong>{{ $sale->invoice_number }}</strong></td>
                    <td>{{ $sale->customer->name ?? 'عميل نقدي' }}</td>
                    <td>{{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $sale->status }}</td>
                    <td>{{ $sale->payment_status }}</td>
                    <td><strong>{{ number_format($sale->total_amount, 2) }} ج.م</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">لا توجد مبيعات في هذه الفترة</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>🏆 أفضل العملاء</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>عدد الفواتير</th>
                    <th>إجمالي المبيعات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesByCustomer as $row)
                <tr>
                    <td>{{ $row['customer']->name ?? '-' }}</td>
                    <td>{{ $row['count'] }}</td>
                    <td>{{ number_format($row['total'], 2) }} ج.م</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">لا توجد بيانات</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($dailySales->count() > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = {!! json_encode($dailySales->keys()->values()) !!};
    const totals = {!! json_encode($dailySales->pluck('total')->values()) !!};

    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'إجمالي المبيعات',
                data: totals,
                borderColor: 'rgb(8, 145, 178)',
                backgroundColor: 'rgba(8, 145, 178, 0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
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
