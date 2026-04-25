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
                <small>{{ $invoice->created_at?->format('Y-m-d') }}</small>
                <small class="ic-cta">عرض التفاصيل ←</small>
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
    /* Editorial KPI strip */
    .kpi-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .kpi {
        position: relative;
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        box-shadow: var(--shadow-soft);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .kpi:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lift);
        border-color: var(--gold-light);
    }
    .kpi::before {
        content: "";
        position: absolute;
        top: 0; right: 0;
        width: 80px; height: 80px;
        background: radial-gradient(circle at top right, rgba(184,153,104,0.12), transparent 70%);
        pointer-events: none;
    }
    .kpi-icon {
        font-size: 1.4rem;
        color: var(--gold);
        opacity: 0.9;
        line-height: 1;
        margin-bottom: 4px;
    }
    .kpi strong {
        display: block;
        font-family: 'Reem Kufi', 'Cairo', sans-serif;
        font-size: 1.7rem;
        font-weight: 700;
        color: var(--teal-deep);
        letter-spacing: -0.02em;
        line-height: 1.1;
    }
    .kpi small {
        color: var(--ink-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 600;
    }

    /* Filters — editorial bar */
    .filters-card {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
        gap: 10px;
        background: var(--paper);
        padding: 16px;
        border-radius: 14px;
        border: 1px solid var(--line);
        margin-bottom: 24px;
        box-shadow: var(--shadow-soft);
        align-items: end;
    }
    .filters-card .form-control {
        height: 42px;
        font-size: 0.88rem;
    }
    .filter-btn { white-space: nowrap; height: 42px; padding: 0 18px; }

    /* Invoice cards — paper documents */
    .invoice-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }
    .invoice-card {
        position: relative;
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 22px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        gap: 12px;
        overflow: hidden;
    }
    .invoice-card::before {
        content: "";
        position: absolute;
        top: 0; right: 0; bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--gold) 0%, var(--gold-light) 60%, transparent 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .invoice-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lift);
        border-color: var(--gold-light);
    }
    .invoice-card:hover::before { opacity: 1; }

    .ic-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        flex-wrap: wrap;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--line);
    }
    .ic-num {
        font-family: 'Reem Kufi', 'Cairo', sans-serif;
        font-weight: 700;
        color: var(--ink);
        font-size: 1.05rem;
        letter-spacing: -0.01em;
    }
    .ic-badges { display: flex; flex-wrap: wrap; gap: 5px; }

    .ic-customer {
        color: var(--ink-soft);
        font-size: 0.92rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ic-event {
        color: var(--ink-muted);
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-style: italic;
    }

    .ic-amounts {
        background: linear-gradient(135deg, var(--cream), #fefbf5);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 12px 14px;
        margin-top: 4px;
    }
    .ic-amount-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        padding: 4px 0;
        font-size: 0.88rem;
    }
    .ic-amount-row + .ic-amount-row {
        border-top: 1px solid rgba(184,153,104,0.18);
    }
    .ic-amount-row span {
        color: var(--ink-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.72rem;
        font-weight: 600;
    }
    .ic-amount-row strong {
        color: var(--ink);
        font-family: 'Reem Kufi', 'Cairo', sans-serif;
        font-weight: 600;
        font-size: 0.96rem;
    }

    .ic-progress {
        background: var(--line);
        height: 3px;
        border-radius: 2px;
        overflow: hidden;
        margin-top: 4px;
    }
    .ic-progress-bar {
        background: linear-gradient(90deg, var(--gold-light), var(--gold));
        height: 100%;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ic-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 8px;
        border-top: 1px solid var(--line);
        margin-top: 4px;
    }
    .ic-foot small {
        color: var(--ink-muted);
        font-size: 0.74rem;
        letter-spacing: 0.04em;
    }
    .ic-cta {
        color: var(--teal-deep) !important;
        font-weight: 600;
        font-size: 0.78rem !important;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    /* Empty state — editorial */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 64px 24px;
        background: var(--paper);
        border-radius: 14px;
        border: 1px dashed var(--gold-light);
        position: relative;
    }
    .empty-state::before {
        content: "✦";
        position: absolute;
        top: 20px; right: 20px;
        color: var(--gold);
        font-size: 1.2rem;
        opacity: 0.4;
    }
    .empty-state::after {
        content: "✦";
        position: absolute;
        bottom: 20px; left: 20px;
        color: var(--gold);
        font-size: 1.2rem;
        opacity: 0.4;
    }
    .empty-state h3 {
        color: var(--ink);
        margin: 16px 0 8px;
        font-family: 'Reem Kufi', 'Cairo', sans-serif;
    }
    .empty-state p { color: var(--ink-muted); margin-bottom: 20px; }

    @media (max-width: 768px) {
        .kpi-strip { grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
        .kpi { padding: 14px; }
        .kpi strong { font-size: 1.2rem; }
        .filters-card { grid-template-columns: 1fr 1fr; padding: 12px; }
        .filter-search { grid-column: 1 / -1; }
        .filter-btn { grid-column: 1 / -1; }
        .invoice-cards { grid-template-columns: 1fr; gap: 12px; }
        .invoice-card { padding: 16px; }
    }
    @media (max-width: 380px) {
        .kpi-strip { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection
