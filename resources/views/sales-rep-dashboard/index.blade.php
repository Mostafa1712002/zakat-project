@extends('layouts.app')

@section('title', 'لوحة تحكم المندوب')

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $salesRep->name }}</h1>
        <p>كود المندوب: {{ $salesRep->code }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales.create') }}" class="btn btn-primary">+ فاتورة جديدة</a>
        <a href="{{ route('sales-rep.collections') }}" class="btn">تحصيل</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">👥</div>
        <div class="stat-value">{{ $stats['total_customers'] }}</div>
        <div class="stat-label">إجمالي العملاء</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">💰</div>
        <div class="stat-value">{{ number_format($stats['monthly_sales']) }} ج.م</div>
        <div class="stat-label">مبيعات الشهر</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">💳</div>
        <div class="stat-value">{{ number_format($stats['monthly_collections']) }} ج.م</div>
        <div class="stat-label">تحصيلات الشهر</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">⏳</div>
        <div class="stat-value">{{ number_format($stats['pending_amount']) }} ج.م</div>
        <div class="stat-label">مستحق التحصيل</div>
    </div>
</div>

<!-- Target Progress & Commission -->
@if($stats['sales_target'] > 0)
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
    <!-- Target Progress -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">تحقيق الهدف البيعي</h3>
            <span class="badge {{ $stats['target_achievement'] >= 100 ? 'badge-success' : ($stats['target_achievement'] >= 75 ? 'badge-warning' : 'badge-danger') }}">
                {{ number_format($stats['target_achievement'], 1) }}%
            </span>
        </div>
        <div class="card-body">
            <div style="background: var(--bg); border-radius: 8px; height: 24px; overflow: hidden;">
                <div style="background: linear-gradient(90deg, var(--primary), var(--primary-dark)); height: 100%; width: {{ min($stats['target_achievement'], 100) }}%; transition: width 0.5s;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 14px; color: var(--text-muted);">
                <span>المحقق: {{ number_format($stats['monthly_sales']) }} ج.م</span>
                <span>الهدف: {{ number_format($stats['sales_target']) }} ج.م</span>
            </div>
            @if(!$stats['has_met_target'] && $stats['remaining_to_target'] > 0)
            <div style="margin-top: 12px; padding: 10px; background: var(--warning-bg, #fff3cd); border-radius: 6px; color: var(--warning-text, #856404);">
                متبقي لتحقيق الهدف: <strong>{{ number_format($stats['remaining_to_target']) }} ج.م</strong>
            </div>
            @endif
        </div>
    </div>

    <!-- Commission Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">العمولة</h3>
            @if($stats['has_met_target'])
            <span class="badge badge-success">مستحقة</span>
            @else
            <span class="badge badge-secondary">غير مستحقة</span>
            @endif
        </div>
        <div class="card-body" style="text-align: center;">
            @if($stats['has_met_target'])
            <div style="font-size: 32px; font-weight: bold; color: var(--success);">
                {{ number_format($stats['commission_earned']) }} ج.م
            </div>
            <div style="font-size: 14px; color: var(--text-muted); margin-top: 8px;">
                @if($stats['commission_type'] === 'percentage')
                عمولة {{ $stats['commission_rate'] }}% من المبيعات
                @else
                عمولة ثابتة
                @endif
            </div>
            @else
            <div style="font-size: 24px; font-weight: bold; color: var(--text-muted);">
                0 ج.م
            </div>
            <div style="font-size: 14px; color: var(--text-muted); margin-top: 8px;">
                حقق الهدف لاستحقاق العمولة
            </div>
            <div style="font-size: 12px; color: var(--primary); margin-top: 4px;">
                @if($stats['commission_type'] === 'percentage')
                العمولة المحتملة: {{ number_format($stats['monthly_sales'] * $stats['commission_rate'] / 100) }} ج.م
                @else
                العمولة المحتملة: {{ number_format($stats['commission_rate']) }} ج.م
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">إجراءات سريعة</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            <a href="{{ route('sales.create') }}" class="btn btn-primary">+ فاتورة بيع</a>
            <a href="{{ route('customers.create') }}" class="btn">+ إضافة عميل</a>
            <a href="{{ route('sales-rep.customers') }}" class="btn">عملائي</a>
            <a href="{{ route('sales-rep.sales') }}" class="btn">فواتيري</a>
            <a href="{{ route('sales-rep.collections') }}" class="btn">تحصيلاتي</a>
            <a href="{{ route('sales-rep.reports') }}" class="btn">تقاريري</a>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">آخر الفواتير</h3>
            <a href="{{ route('sales-rep.sales') }}" class="btn btn-sm">عرض الكل</a>
        </div>
        <div class="card-body">
            @forelse($recentSales as $sale)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                <div>
                    <span class="text-primary font-bold">{{ $sale->invoice_number }}</span>
                    <br>
                    <small class="text-muted">{{ $sale->customer?->name ?? 'عميل نقدي' }}</small>
                </div>
                <div style="text-align: left;">
                    <strong>{{ number_format($sale->total_amount) }} ج.م</strong>
                    <br>
                    <span class="badge badge-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
                        {{ $sale->payment_status === 'paid' ? 'مدفوع' : ($sale->payment_status === 'partial' ? 'جزئي' : 'غير مدفوع') }}
                    </span>
                </div>
            </div>
            @empty
            <div class="empty-state" style="padding: 20px; text-align: center;">
                <p class="text-muted">لا توجد فواتير حديثة</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Collections -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">آخر التحصيلات</h3>
            <a href="{{ route('sales-rep.collections') }}" class="btn btn-sm">عرض الكل</a>
        </div>
        <div class="card-body">
            @forelse($recentPayments as $payment)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                <div>
                    <span class="font-bold">{{ $payment->payment_number }}</span>
                    <br>
                    <small class="text-muted">{{ $payment->payable?->name ?? '-' }}</small>
                </div>
                <div style="text-align: left;">
                    <strong class="text-success">{{ number_format($payment->amount) }} ج.م</strong>
                    <br>
                    <small class="text-muted">{{ $payment->payment_date->format('Y-m-d') }}</small>
                </div>
            </div>
            @empty
            <div class="empty-state" style="padding: 20px; text-align: center;">
                <p class="text-muted">لا توجد تحصيلات حديثة</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Pending Customers -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">العملاء المستحق عليهم</h3>
        <a href="{{ route('sales-rep.customers') }}" class="btn btn-sm">عرض الكل</a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>الهاتف</th>
                    <th>الرصيد المستحق</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingCustomers as $customer)
                <tr>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->phone ?? $customer->mobile ?? '-' }}</td>
                    <td><span class="badge badge-danger">{{ number_format($customer->current_balance) }} ج.م</span></td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm">عرض</a>
                        <a href="{{ route('customers.collect', $customer) }}" class="btn btn-sm btn-primary">تحصيل</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">لا يوجد عملاء مستحق عليهم</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Warehouses -->
@if($warehouses->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">المخازن المتاحة</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            @foreach($warehouses as $warehouse)
            <div style="padding: 12px 20px; background: var(--bg); border-radius: 8px;">
                <strong>{{ $warehouse->name }}</strong>
                @if($warehouse->pivot->is_default)
                <span class="badge badge-primary" style="margin-right: 8px;">افتراضي</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }
    .mt-4 { margin-top: 20px; }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    }

    @media (max-width: 900px) {
        div[style*="grid-template-columns: repeat(2, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
