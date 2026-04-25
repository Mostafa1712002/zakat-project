@extends('layouts.app')

@section('title', 'الفواتير')

@section('content')
@php
    $statusLabels = [
        'draft'        => ['مسودة', 'secondary', '📝'],
        'issued'       => ['مصدرة', 'primary', '✅'],
        'paid_partial' => ['مدفوعة جزئياً', 'warning', '⏳'],
        'paid'         => ['مدفوعة', 'success', '💰'],
        'cancelled'    => ['ملغاة', 'danger', '❌'],
    ];
    $zatcaLabels = [
        'pending'  => ['قيد الإرسال', 'secondary', '⏱️'],
        'cleared'  => ['مرحّلة', 'success', '✅'],
        'reported' => ['مبلَّغة', 'info', '📨'],
        'failed'   => ['فشل', 'danger', '⚠️'],
    ];
    $totalAmount = $invoices->sum('grand_total');
    $paidAmount = $invoices->sum('paid_amount');
    $clearedCount = $invoices->where('zatca_status', 'cleared')->count() + $invoices->where('zatca_status', 'reported')->count();
@endphp

<div class="page-header">
    <div>
        <h1>🧾 الفواتير</h1>
        <p>إدارة الفواتير وإصدارها وإرسالها لزاتكا</p>
    </div>
    @can('create', \App\Domain\Sales\Models\Invoice::class)
        <div class="header-actions">
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">➕ فاتورة جديدة</a>
        </div>
    @endcan
</div>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="kpi-strip">
    <div class="kpi"><span class="kpi-icon">📊</span><div><strong>{{ $invoices->total() }}</strong><small>إجمالي الفواتير</small></div></div>
    <div class="kpi"><span class="kpi-icon">💰</span><div><strong>{{ number_format($totalAmount, 0) }}</strong><small>القيمة الكلية (ر.س)</small></div></div>
    <div class="kpi"><span class="kpi-icon">✅</span><div><strong>{{ number_format($paidAmount, 0) }}</strong><small>المُحصَّل (ر.س)</small></div></div>
    <div class="kpi"><span class="kpi-icon">📨</span><div><strong>{{ $clearedCount }}</strong><small>مقبولة من زاتكا</small></div></div>
</div>

<form method="GET" class="filters-card">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="🔍 بحث برقم الفاتورة أو اسم الفعالية" class="form-control filter-search">

    <select name="status" class="form-control">
        <option value="">— كل الحالات —</option>
        @foreach ($statusLabels as $key => [$label, $_, $emoji])
            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $emoji }} {{ $label }}</option>
        @endforeach
    </select>

    <select name="zatca_status" class="form-control">
        <option value="">— كل حالات زاتكا —</option>
        @foreach ($zatcaLabels as $key => [$label, $_, $emoji])
            <option value="{{ $key }}" @selected(request('zatca_status') === $key)>{{ $emoji }} {{ $label }}</option>
        @endforeach
    </select>

    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" placeholder="من تاريخ">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" placeholder="إلى تاريخ">

    <button class="btn btn-primary filter-btn">🔍 تصفية</button>
</form>

<div class="invoice-cards">
    @forelse ($invoices as $invoice)
        @php
            [$slab, $scol, $semoji] = $statusLabels[$invoice->status] ?? [$invoice->status, 'secondary', ''];
            [$zlab, $zcol, $zemoji] = $zatcaLabels[$invoice->zatca_status] ?? [$invoice->zatca_status, 'secondary', ''];
            $remaining = (float) $invoice->grand_total - (float) $invoice->paid_amount;
            $paidPct = (float) $invoice->grand_total > 0
                ? min(100, round(((float) $invoice->paid_amount / (float) $invoice->grand_total) * 100))
                : 0;
        @endphp
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="invoice-card">
            <div class="ic-head">
                <div class="ic-num">{{ $invoice->invoice_number }}</div>
                <div class="ic-badges">
                    <span class="badge badge-{{ $scol }}">{{ $semoji }} {{ $slab }}</span>
                    <span class="badge badge-{{ $zcol }}">{{ $zemoji }} {{ $zlab }}</span>
                </div>
            </div>
            <div class="ic-customer">👤 {{ $invoice->customer?->name ?? '—' }}</div>
            <div class="ic-event">🎯 {{ $invoice->event_name }}</div>
            <div class="ic-amounts">
                <div class="ic-amount-row">
                    <span>الإجمالي</span>
                    <strong>{{ number_format((float) $invoice->grand_total, 2) }} ر.س</strong>
                </div>
                @if ((float) $invoice->paid_amount > 0)
                    <div class="ic-amount-row">
                        <span>المدفوع</span>
                        <strong style="color:#16a34a">{{ number_format((float) $invoice->paid_amount, 2) }} ر.س</strong>
                    </div>
                @endif
                @if ($remaining > 0 && $invoice->status !== 'draft')
                    <div class="ic-amount-row">
                        <span>المتبقي</span>
                        <strong style="color:#d97706">{{ number_format($remaining, 2) }} ر.س</strong>
                    </div>
                @endif
            </div>
            @if ($invoice->grand_total > 0 && $invoice->status !== 'draft')
                <div class="ic-progress">
                    <div class="ic-progress-bar" style="width:{{ $paidPct }}%"></div>
                </div>
            @endif
            <div class="ic-foot">
                <small>📅 {{ $invoice->created_at?->format('Y-m-d') }}</small>
                <small style="color:var(--primary)">عرض التفاصيل ←</small>
            </div>
        </a>
    @empty
        <div class="empty-state">
            <div style="font-size:3rem">📭</div>
            <h3>لا توجد فواتير</h3>
            <p>ابدأ بإنشاء فاتورة جديدة</p>
            @can('create', \App\Domain\Sales\Models\Invoice::class)
                <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">➕ فاتورة جديدة</a>
            @endcan
        </div>
    @endforelse
</div>

{{ $invoices->links() }}

@push('styles')
<style>
    .kpi-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }
    .kpi {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .kpi-icon {
        font-size: 1.8rem;
        background: rgba(14,116,144,0.1);
        width: 44px; height: 44px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 10px;
    }
    .kpi strong { display: block; font-size: 1.15rem; color: #0f172a; }
    .kpi small { color: #64748b; font-size: 0.8rem; }

    .filters-card {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
        gap: 8px;
        background: white;
        padding: 14px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }
    .filter-btn { white-space: nowrap; }

    .invoice-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .invoice-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s;
        display: flex; flex-direction: column; gap: 8px;
    }
    .invoice-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(14,116,144,0.12);
        border-color: #0e7490;
    }
    .ic-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; flex-wrap: wrap; }
    .ic-num { font-weight: 700; color: #0f172a; font-size: 1rem; }
    .ic-badges { display: flex; flex-wrap: wrap; gap: 4px; }
    .ic-customer { color: #475569; font-size: 0.9rem; }
    .ic-event { color: #64748b; font-size: 0.85rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ic-amounts {
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px 12px;
        margin-top: 4px;
    }
    .ic-amount-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 0.88rem;
    }
    .ic-amount-row span { color: #64748b; }
    .ic-amount-row strong { color: #0f172a; }
    .ic-progress { background: #e2e8f0; height: 4px; border-radius: 2px; overflow: hidden; margin-top: 2px; }
    .ic-progress-bar { background: linear-gradient(90deg, #16a34a, #22c55e); height: 100%; transition: width 0.4s; }
    .ic-foot {
        display: flex;
        justify-content: space-between;
        padding-top: 8px;
        border-top: 1px solid #f1f5f9;
        margin-top: 4px;
    }
    .ic-foot small { color: #94a3b8; font-size: 0.78rem; }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 48px 16px;
        background: #f8fafc;
        border-radius: 12px;
        border: 2px dashed #cbd5e1;
    }
    .empty-state h3 { color: #475569; margin: 12px 0 4px; }
    .empty-state p { color: #94a3b8; margin-bottom: 16px; }

    @media (max-width: 768px) {
        .kpi-strip { grid-template-columns: 1fr 1fr; gap: 8px; }
        .kpi { padding: 10px 12px; gap: 8px; }
        .kpi-icon { width: 36px; height: 36px; font-size: 1.4rem; }
        .kpi strong { font-size: 0.95rem; }
        .kpi small { font-size: 0.7rem; }
        .filters-card { grid-template-columns: 1fr 1fr; }
        .filter-search { grid-column: 1 / -1; }
        .filter-btn { grid-column: 1 / -1; }
        .invoice-cards { grid-template-columns: 1fr; gap: 10px; }
    }
    @media (max-width: 380px) {
        .kpi-strip { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection
