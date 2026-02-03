@extends('layouts.app')

@section('title', 'تقاريري')

@section('content')
<div class="page-header">
    <div>
        <h1>تقارير الأداء</h1>
        <p>تقارير مبيعاتك وتحصيلاتك</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-rep.dashboard') }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- Year Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('sales-rep.reports') }}" method="GET" style="display: flex; gap: 12px; align-items: flex-end;">
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

<!-- Total Commission Summary -->
@if($salesRep->sales_target > 0)
<div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-light, #e3f2fd), var(--bg)); border-color: var(--primary);">
    <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0; color: var(--primary);">إجمالي العمولات المستحقة لسنة {{ $year }}</h3>
            <p style="margin: 4px 0 0; font-size: 14px; color: var(--text-muted);">
                العمولة تُحسب عند تحقيق الهدف الشهري ({{ number_format($salesRep->sales_target) }} ج.م)
            </p>
        </div>
        <div style="font-size: 28px; font-weight: bold; color: var(--success);">
            {{ number_format($totalCommission) }} ج.م
        </div>
    </div>
</div>
@endif

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
                    <th>إجمالي المبيعات</th>
                    <th>تحقيق الهدف</th>
                    <th>إجمالي التحصيلات</th>
                    <th>نسبة التحصيل</th>
                    <th>العمولة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlySales as $monthly)
                <tr>
                    <td><strong>{{ $monthly['month_name'] }}</strong></td>
                    <td>{{ $monthly['invoices_count'] }}</td>
                    <td>{{ number_format($monthly['sales']) }} ج.م</td>
                    <td>
                        @if($salesRep->sales_target > 0)
                        <span class="badge badge-{{ $monthly['has_met_target'] ? 'success' : ($monthly['target_achievement'] >= 75 ? 'warning' : 'secondary') }}">
                            {{ number_format($monthly['target_achievement'], 0) }}%
                        </span>
                        @else
                        -
                        @endif
                    </td>
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
                    <td>
                        @if($monthly['has_met_target'])
                        <span style="color: var(--success); font-weight: bold;">{{ number_format($monthly['commission']) }} ج.م</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="background: var(--bg); font-weight: bold;">
                <tr>
                    <td>الإجمالي</td>
                    <td>{{ $monthlySales->sum('invoices_count') }}</td>
                    <td>{{ number_format($monthlySales->sum('sales')) }} ج.م</td>
                    <td>-</td>
                    <td>{{ number_format($monthlySales->sum('collections')) }} ج.م</td>
                    <td>
                        @if($monthlySales->sum('sales') > 0)
                        @php $totalRate = ($monthlySales->sum('collections') / $monthlySales->sum('sales')) * 100; @endphp
                        {{ number_format($totalRate, 1) }}%
                        @else
                        -
                        @endif
                    </td>
                    <td style="color: var(--success);">{{ number_format($totalCommission) }} ج.م</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

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
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }
</style>
@endpush
