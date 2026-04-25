@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
@php
    $statusLabels = [
        'draft'         => ['مسودة', '#94a3b8'],
        'issued'        => ['مُصدرة', '#0ea5e9'],
        'paid_partial'  => ['مدفوعة جزئياً', '#f59e0b'],
        'paid'          => ['مدفوعة', '#10b981'],
        'cancelled'     => ['ملغاة', '#ef4444'],
    ];
    $maxTrend = max(array_column($revenue_trend, 'value')) ?: 1;
    $totalStatusCount = array_sum($invoice_status_breakdown) ?: 1;
@endphp

<div class="page-header">
    <div>
        <h1>📊 لوحة التحكم</h1>
        <p>نظرة عامة على نشاط المنصة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary">📑 عرض سعر جديد</a>
        <a href="{{ route('admin.invoices.index') }}" class="btn">🧾 الفواتير</a>
    </div>
</div>

<!-- KPI Cards -->
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-icon success">💰</div>
        <div class="stat-value">{{ number_format($monthly_revenue, 2) }}</div>
        <div class="stat-label">إيرادات الشهر الحالي (ر.س)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">⚠️</div>
        <div class="stat-value">{{ number_format($outstanding_debt, 2) }}</div>
        <div class="stat-label">المتأخرات (ر.س)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">💵</div>
        <div class="stat-value">{{ number_format($paid_this_month, 2) }}</div>
        <div class="stat-label">تم تحصيله هذا الشهر (ر.س)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">📅</div>
        <div class="stat-value">{{ $upcoming_events->count() }}</div>
        <div class="stat-label">فعاليات قادمة</div>
    </div>
</div>

<!-- Revenue Trend (12 months) + Status Breakdown -->
<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3 class="card-title">📈 الإيرادات الشهرية (آخر 12 شهراً)</h3></div>
        <div class="card-body">
            <div class="bar-chart">
                @foreach ($revenue_trend as $point)
                    @php $pct = $maxTrend > 0 ? round(($point['value'] / $maxTrend) * 100) : 0; @endphp
                    <div class="bar-row" title="{{ $point['label'] }}: {{ number_format($point['value'], 2) }} ر.س">
                        <span class="bar-label">{{ $point['label'] }}</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ max($pct, 1) }}%;"></div>
                        </div>
                        <span class="bar-value">{{ number_format($point['value'], 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">🥧 توزيع حالات الفواتير</h3></div>
        <div class="card-body">
            <div class="status-breakdown">
                @forelse ($invoice_status_breakdown as $status => $count)
                    @php
                        [$label, $color] = $statusLabels[$status] ?? [$status, '#64748b'];
                        $pct = round(($count / $totalStatusCount) * 100);
                    @endphp
                    <div class="status-row">
                        <span class="status-swatch" style="background: {{ $color }};"></span>
                        <span class="status-label">{{ $label }}</span>
                        <div class="status-bar">
                            <div class="status-bar-fill" style="background: {{ $color }}; width: {{ $pct }}%;"></div>
                        </div>
                        <span class="status-count">{{ $count }} ({{ $pct }}%)</span>
                    </div>
                @empty
                    <p class="muted">لا توجد فواتير بعد.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3 class="card-title">🧾 آخر الفواتير</h3></div>
        <div class="card-body">
            @if ($recent_invoices->isEmpty())
                <p class="muted">لا توجد فواتير بعد.</p>
            @else
                <table class="table table-sm">
                    <thead><tr><th>الرقم</th><th>العميل</th><th>الإجمالي</th><th>الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($recent_invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('admin.invoices.show', $inv) }}">{{ $inv->invoice_number }}</a>
                            </td>
                            <td>{{ optional($inv->customer)->name ?? '—' }}</td>
                            <td>{{ number_format($inv->grand_total, 2) }}</td>
                            <td>{{ ($statusLabels[$inv->status] ?? [$inv->status])[0] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">💵 آخر التحصيلات</h3></div>
        <div class="card-body">
            @if ($recent_payments->isEmpty())
                <p class="muted">لا توجد تحصيلات بعد.</p>
            @else
                <table class="table table-sm">
                    <thead><tr><th>الرقم</th><th>العميل</th><th>الفاتورة</th><th>المبلغ</th></tr></thead>
                    <tbody>
                    @foreach ($recent_payments as $p)
                        <tr>
                            <td>{{ $p->payment_number }}</td>
                            <td>{{ optional($p->customer)->name ?? '—' }}</td>
                            <td>{{ optional($p->invoice)->invoice_number ?? '—' }}</td>
                            <td>{{ number_format($p->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<!-- Upcoming Events -->
@if ($upcoming_events->isNotEmpty())
<div class="card">
    <div class="card-header"><h3 class="card-title">📅 الفعاليات القادمة</h3></div>
    <div class="card-body">
        <table class="table table-sm">
            <thead><tr><th>التاريخ</th><th>الفاتورة</th><th>العميل</th><th>الفعالية</th><th>الموقع</th></tr></thead>
            <tbody>
            @foreach ($upcoming_events as $ev)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($ev->event_start_date)->format('Y-m-d') }}</td>
                    <td><a href="{{ route('admin.invoices.show', $ev) }}">{{ $ev->invoice_number }}</a></td>
                    <td>{{ optional($ev->customer)->name ?? '—' }}</td>
                    <td>{{ $ev->event_name }}</td>
                    <td>{{ $ev->event_location }}</td>
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
    .stats-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    @media (max-width: 1024px) { .stats-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 600px)  { .stats-grid-4 { grid-template-columns: 1fr; } }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    @media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } }

    .bar-chart { display: flex; flex-direction: column; gap: 6px; }
    .bar-row { display: grid; grid-template-columns: 70px 1fr 70px; gap: 8px; align-items: center; font-size: 0.85rem; }
    .bar-label { color: #64748b; }
    .bar-track { background: #f1f5f9; border-radius: 4px; height: 18px; overflow: hidden; }
    .bar-fill { background: linear-gradient(90deg, #3b82f6, #06b6d4); height: 100%; border-radius: 4px; }
    .bar-value { text-align: end; color: #475569; }

    .status-breakdown { display: flex; flex-direction: column; gap: 10px; }
    .status-row { display: grid; grid-template-columns: 14px 110px 1fr 80px; gap: 8px; align-items: center; font-size: 0.9rem; }
    .status-swatch { width: 14px; height: 14px; border-radius: 3px; }
    .status-bar { background: #f1f5f9; border-radius: 4px; height: 12px; overflow: hidden; }
    .status-bar-fill { height: 100%; border-radius: 4px; }
    .status-count { text-align: end; color: #475569; }

    .muted { color: #94a3b8; text-align: center; padding: 16px; }
    .table-sm th, .table-sm td { padding: 6px 8px; font-size: 0.88rem; }
</style>
@endpush
