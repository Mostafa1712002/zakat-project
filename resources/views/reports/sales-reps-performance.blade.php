@extends('layouts.app')

@section('title', 'تقرير أداء المندوبين')

@section('content')
<div class="page-header">
    <div>
        <h1>تقرير أداء المندوبين</h1>
        <p>عرض أداء جميع مندوبي المبيعات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('reports.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reports.sales-reps') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <button type="submit" class="btn btn-primary">عرض التقرير</button>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">👥</div>
        <div class="stat-value">{{ $totalSalesReps }}</div>
        <div class="stat-label">إجمالي المندوبين</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">📊</div>
        <div class="stat-value">{{ $activeSalesReps }}</div>
        <div class="stat-label">المندوبين النشطين</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">💰</div>
        <div class="stat-value">{{ number_format($totalSales) }} ج.م</div>
        <div class="stat-label">إجمالي المبيعات</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">💳</div>
        <div class="stat-value">{{ number_format($totalCollections) }} ج.م</div>
        <div class="stat-label">إجمالي التحصيلات</div>
    </div>
</div>

<!-- Sales Reps Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">جدول أداء المندوبين</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المندوب</th>
                    <th>الكود</th>
                    <th>الفرع</th>
                    <th>عدد العملاء</th>
                    <th>عدد الفواتير</th>
                    <th>إجمالي المبيعات</th>
                    <th>إجمالي التحصيلات</th>
                    <th>نسبة التحصيل</th>
                    <th>الهدف</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesReps as $index => $rep)
                <tr>
                    <td>
                        @if($index < 3 && $rep->sales_sum_total_amount > 0)
                        <span style="font-size: 20px;">{{ ['1' => '🥇', '2' => '🥈', '3' => '🥉'][$index + 1] ?? '' }}</span>
                        @else
                        {{ $index + 1 }}
                        @endif
                    </td>
                    <td>
                        <strong>{{ $rep->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $rep->user?->email ?? '-' }}</small>
                    </td>
                    <td>{{ $rep->code }}</td>
                    <td>{{ $rep->branch?->name ?? '-' }}</td>
                    <td>{{ $rep->customers_count }}</td>
                    <td>{{ $rep->sales_count }}</td>
                    <td>{{ number_format($rep->sales_sum_total_amount ?? 0) }} ج.م</td>
                    <td>{{ number_format($rep->payments_sum_amount ?? 0) }} ج.م</td>
                    <td>
                        @php
                            $rate = ($rep->sales_sum_total_amount ?? 0) > 0
                                ? (($rep->payments_sum_amount ?? 0) / $rep->sales_sum_total_amount) * 100
                                : 0;
                        @endphp
                        <span class="badge badge-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}">
                            {{ number_format($rate, 1) }}%
                        </span>
                    </td>
                    <td>
                        @if($rep->sales_target > 0)
                        @php
                            $achievement = (($rep->sales_sum_total_amount ?? 0) / $rep->sales_target) * 100;
                        @endphp
                        <span class="badge badge-{{ $achievement >= 100 ? 'success' : ($achievement >= 75 ? 'warning' : 'danger') }}">
                            {{ number_format($achievement, 1) }}%
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('reports.sales-rep-detail', $rep) }}" class="btn btn-sm btn-primary">تفاصيل</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">لا يوجد مندوبين</td>
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
