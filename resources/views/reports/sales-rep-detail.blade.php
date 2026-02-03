@extends('layouts.app')

@section('title', 'تقرير المندوب - ' . $salesRep->name)

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $salesRep->name }}</h1>
        <p>كود المندوب: {{ $salesRep->code }} | الفرع: {{ $salesRep->branch?->name ?? '-' }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('reports.sales-reps') }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- Year Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reports.sales-rep-detail', $salesRep) }}" method="GET" style="display: flex; gap: 12px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">السنة</label>
                <select name="year" class="form-control">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-primary">عرض</button>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">👥</div>
        <div class="stat-value">{{ $salesRep->customers()->count() }}</div>
        <div class="stat-label">إجمالي العملاء</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">💰</div>
        <div class="stat-value">{{ number_format($totalSalesAmount) }} ج.م</div>
        <div class="stat-label">إجمالي المبيعات {{ $year }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">💳</div>
        <div class="stat-value">{{ number_format($totalCollectionsAmount) }} ج.م</div>
        <div class="stat-label">إجمالي التحصيلات {{ $year }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $collectionRate >= 80 ? 'success' : ($collectionRate >= 50 ? 'warning' : 'danger') }}">📊</div>
        <div class="stat-value">{{ number_format($collectionRate, 1) }}%</div>
        <div class="stat-label">نسبة التحصيل</div>
    </div>
</div>

<!-- Target Achievement -->
@if($salesRep->sales_target > 0)
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">تحقيق الهدف البيعي</h3>
        <span class="badge {{ $targetAchievement >= 100 ? 'badge-success' : ($targetAchievement >= 75 ? 'badge-warning' : 'badge-danger') }}">
            {{ number_format($targetAchievement, 1) }}%
        </span>
    </div>
    <div class="card-body">
        <div style="background: var(--bg); border-radius: 8px; height: 24px; overflow: hidden;">
            <div style="background: linear-gradient(90deg, var(--primary), var(--primary-dark)); height: 100%; width: {{ min($targetAchievement, 100) }}%; transition: width 0.5s;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 14px; color: var(--text-muted);">
            <span>المحقق: {{ number_format($totalSalesAmount) }} ج.م</span>
            <span>الهدف: {{ number_format($salesRep->sales_target) }} ج.م</span>
        </div>
    </div>
</div>
@endif

<!-- Sales Rep Info -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">معلومات المندوب</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <span class="text-muted">البريد الإلكتروني:</span>
                <strong>{{ $salesRep->user?->email ?? $salesRep->email ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-muted">الهاتف:</span>
                <strong>{{ $salesRep->phone ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-muted">نسبة العمولة:</span>
                <strong>{{ $salesRep->commission_rate }}{{ $salesRep->commission_type === 'percentage' ? '%' : ' ج.م' }}</strong>
            </div>
            <div>
                <span class="text-muted">المخازن:</span>
                <strong>{{ $salesRep->warehouses->pluck('name')->join(', ') ?: '-' }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Performance -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">الأداء الشهري لسنة {{ $year }}</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>الشهر</th>
                    <th>عدد الفواتير</th>
                    <th>عملاء جدد</th>
                    <th>إجمالي المبيعات</th>
                    <th>إجمالي التحصيلات</th>
                    <th>نسبة التحصيل</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlySales as $monthly)
                <tr>
                    <td><strong>{{ $monthly['month_name'] }}</strong></td>
                    <td>{{ $monthly['invoices_count'] }}</td>
                    <td>{{ $monthly['new_customers'] }}</td>
                    <td>{{ number_format($monthly['sales']) }} ج.م</td>
                    <td>{{ number_format($monthly['collections']) }} ج.م</td>
                    <td>
                        @if($monthly['sales'] > 0)
                        @php $rate = ($monthly['collections'] / $monthly['sales']) * 100; @endphp
                        <span class="badge badge-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}">
                            {{ number_format($rate, 1) }}%
                        </span>
                        @else
                        -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="background: var(--bg); font-weight: bold;">
                <tr>
                    <td>الإجمالي</td>
                    <td>{{ $monthlySales->sum('invoices_count') }}</td>
                    <td>{{ $monthlySales->sum('new_customers') }}</td>
                    <td>{{ number_format($monthlySales->sum('sales')) }} ج.م</td>
                    <td>{{ number_format($monthlySales->sum('collections')) }} ج.م</td>
                    <td>{{ number_format($collectionRate, 1) }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Two Column Layout -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
    <!-- Top Customers -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">أفضل العملاء</h3>
        </div>
        <div class="table-container overflow-auto">
            <table class="table text-nowrap">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العميل</th>
                        <th>إجمالي المبيعات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $index => $customer)
                    <tr>
                        <td>
                            @if($index < 3)
                            <span style="font-size: 20px;">{{ ['1' => '🥇', '2' => '🥈', '3' => '🥉'][$index + 1] ?? '' }}</span>
                            @else
                            {{ $index + 1 }}
                            @endif
                        </td>
                        <td><strong>{{ $customer->name }}</strong></td>
                        <td>{{ number_format($customer->sales_sum_total_amount ?? 0) }} ج.م</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">لا توجد بيانات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">آخر الفواتير</h3>
        </div>
        <div class="table-container overflow-auto">
            <table class="table text-nowrap">
                <thead>
                    <tr>
                        <th>الفاتورة</th>
                        <th>العميل</th>
                        <th>المبلغ</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                    <tr>
                        <td>
                            <a href="{{ route('sales.show', $sale) }}" class="text-primary">
                                {{ $sale->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $sale->customer?->name ?? 'عميل نقدي' }}</td>
                        <td>{{ number_format($sale->total_amount) }} ج.م</td>
                        <td>
                            <span class="badge badge-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                {{ $sale->payment_status === 'paid' ? 'مدفوع' : ($sale->payment_status === 'partial' ? 'جزئي' : 'غير مدفوع') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">لا توجد فواتير</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }

    @media (max-width: 900px) {
        div[style*="grid-template-columns: repeat(2, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
