@extends('layouts.app')

@section('title', 'الخزنة')

@section('content')
<div class="page-header">
    <div>
        <h1>🏦 الخزنة</h1>
        <p>متابعة الرصيد النقدي والحركات المالية</p>
    </div>
</div>

<!-- فلتر التاريخ -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('treasury.index') }}" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">🔍 عرض</button>
            <a href="{{ route('treasury.index') }}" class="btn">🔄 إعادة تعيين</a>
        </form>
    </div>
</div>

<!-- الرصيد الفعلي الحالي -->
<div class="card mb-4" style="background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); color: white;">
    <div class="card-body" style="text-align: center; padding: 30px;">
        <div style="font-size: 18px; opacity: 0.9; margin-bottom: 8px;">💰 الرصيد الفعلي الحالي</div>
        <div style="font-size: 42px; font-weight: 700;">{{ number_format($overallBalance, 2) }} ج.م</div>
        @if($openingBalance > 0)
        <div style="font-size: 14px; opacity: 0.8; margin-top: 8px;">رأس المال: {{ number_format($openingBalance, 2) }} ج.م</div>
        @endif
    </div>
</div>

<!-- إحصائيات الفترة -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card" style="border-right: 4px solid var(--success);">
        <div class="stat-icon" style="color: var(--success);">📥</div>
        <div class="stat-value" style="color: var(--success);">{{ number_format($totalIncome, 2) }} ج.م</div>
        <div class="stat-label">إجمالي الوارد</div>
        <div style="margin-top: 12px; font-size: 13px; color: var(--text-muted);">
            <div>تحصيلات: {{ number_format($collections, 2) }} ج.م</div>
            <div>مبيعات نقدية: {{ number_format($cashSales, 2) }} ج.م</div>
            <div>سحب خزينات المندوبين: {{ number_format($repWithdrawals, 2) }} ج.م</div>
        </div>
    </div>

    <div class="stat-card" style="border-right: 4px solid var(--danger);">
        <div class="stat-icon" style="color: var(--danger);">📤</div>
        <div class="stat-value" style="color: var(--danger);">{{ number_format($totalExpenses, 2) }} ج.م</div>
        <div class="stat-label">إجمالي الصادر</div>
        <div style="margin-top: 12px; font-size: 13px; color: var(--text-muted);">
            <div>مصروفات: {{ number_format($expenses, 2) }} ج.م</div>
            <div>مدفوعات موردين: {{ number_format($supplierPayments, 2) }} ج.م</div>
        </div>
    </div>

    <div class="stat-card" style="border-right: 4px solid var(--primary);">
        <div class="stat-icon" style="color: var(--primary);">📈</div>
        <div class="stat-value" style="color: {{ $profit >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ number_format($profit, 2) }} ج.م</div>
        <div class="stat-label">الربح</div>
        <div style="margin-top: 12px; font-size: 13px; color: var(--text-muted);">
            أرباح المبيعات في الفترة
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
    <!-- تفاصيل المصروفات -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📊 المصروفات حسب الفئة</h3>
        </div>
        <div class="card-body">
            @forelse($expensesByCategory as $category => $amount)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                    <span>{{ $category }}</span>
                    <strong style="color: var(--danger);">{{ number_format($amount, 2) }} ج.م</strong>
                </div>
            @empty
                <div class="empty-state" style="padding: 30px;">
                    <div class="empty-state-icon">📭</div>
                    <p>لا توجد مصروفات في هذه الفترة</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- آخر الحركات -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📋 آخر الحركات</h3>
        </div>
        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
            @forelse($recentTransactions as $transaction)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                    <div>
                        <div style="font-weight: 600;">
                            @if($transaction['type'] === 'income')
                                <span style="color: var(--success);">📥</span>
                            @else
                                <span style="color: var(--danger);">📤</span>
                            @endif
                            {{ $transaction['description'] }}
                        </div>
                        <small style="color: var(--text-muted);">
                            {{ \Carbon\Carbon::parse($transaction['date'])->format('Y/m/d') }}
                            @if($transaction['method'])
                                •
                                @switch($transaction['method'])
                                    @case('cash') نقدي @break
                                    @case('bank_transfer') تحويل بنكي @break
                                    @case('check') شيك @break
                                    @default {{ $transaction['method'] }}
                                @endswitch
                            @endif
                        </small>
                    </div>
                    <strong style="color: {{ $transaction['type'] === 'income' ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $transaction['type'] === 'income' ? '+' : '-' }}{{ number_format($transaction['amount'], 2) }} ج.م
                    </strong>
                </div>
            @empty
                <div class="empty-state" style="padding: 30px;">
                    <div class="empty-state-icon">📭</div>
                    <p>لا توجد حركات في هذه الفترة</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- الفواتير المتأخرة -->
@if(isset($overdueInvoices) && $overdueInvoices->count() > 0)
<div class="card mt-4">
    <div class="card-header" style="background: #fef2f2; border-bottom: 2px solid #ef4444;">
        <h3 class="card-title" style="color: #dc2626;">⚠️ فواتير متأخرة السداد ({{ number_format($totalOverdueAmount, 2) }} ج.م)</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>تاريخ الاستحقاق</th>
                    <th>المتبقي</th>
                    <th>أيام التأخير</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($overdueInvoices as $invoice)
                <tr style="background: #fef2f2;">
                    <td><code>{{ $invoice->invoice_number }}</code></td>
                    <td>{{ $invoice->customer->name ?? '-' }}</td>
                    <td>{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</td>
                    <td><strong class="text-danger">{{ number_format($invoice->remaining_amount, 2) }} ج.م</strong></td>
                    <td>
                        @if($invoice->due_date)
                            <span class="badge badge-danger">{{ $invoice->due_date->diffInDays(now()) }} يوم</span>
                        @endif
                    </td>
                    <td>
                        @if($invoice->customer)
                            <a href="{{ route('customers.collect.form', $invoice->customer) }}" class="btn btn-sm btn-success">تحصيل</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- فواتير مستحقة قريباً -->
@if(isset($upcomingDueInvoices) && $upcomingDueInvoices->count() > 0)
<div class="card mt-4">
    <div class="card-header" style="background: #fefce8; border-bottom: 2px solid #f59e0b;">
        <h3 class="card-title" style="color: #d97706;">📅 فواتير مستحقة خلال 7 أيام</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>تاريخ الاستحقاق</th>
                    <th>المتبقي</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingDueInvoices as $invoice)
                <tr style="background: #fefce8;">
                    <td><code>{{ $invoice->invoice_number }}</code></td>
                    <td>{{ $invoice->customer->name ?? '-' }}</td>
                    <td>{{ $invoice->due_date?->format('Y-m-d') }}</td>
                    <td><strong>{{ number_format($invoice->remaining_amount, 2) }} ج.م</strong></td>
                    <td>
                        @if($invoice->customer)
                            <a href="{{ route('customers.collect.form', $invoice->customer) }}" class="btn btn-sm btn-success">تحصيل</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- روابط سريعة -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">⚡ إجراءات سريعة</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            <a href="{{ route('expenses.create') }}" class="btn btn-danger">💸 تسجيل مصروف</a>
            <a href="{{ route('payments.index') }}" class="btn btn-success">💵 التحصيلات</a>
            <a href="{{ route('sales.create') }}" class="btn btn-primary">💰 فاتورة بيع</a>
            <a href="{{ route('employee-transactions.create') }}" class="btn">👨‍💻 صرف مرتب/سلفة</a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }
    .mt-4 { margin-top: 20px; }

    @media (max-width: 900px) {
        .stats-grid { grid-template-columns: 1fr !important; }
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
