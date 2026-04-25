@extends('layouts.app')

@section('title', $invoice->invoice_number)

@section('content')
@php
    $statusLabels = [
        'draft'        => ['مسودة', 'secondary'],
        'issued'       => ['مصدرة', 'primary'],
        'paid_partial' => ['مدفوعة جزئياً', 'warning'],
        'paid'         => ['مدفوعة', 'success'],
        'cancelled'    => ['ملغاة', 'danger'],
    ];
    $zatcaLabels = [
        'pending'  => ['قيد الإرسال', 'secondary'],
        'cleared'  => ['مرحّلة', 'success'],
        'reported' => ['مبلَّغة', 'info'],
        'failed'   => ['فشل', 'danger'],
    ];
    [$label, $color] = $statusLabels[$invoice->status] ?? [$invoice->status, 'secondary'];
    [$zlab, $zcol] = $zatcaLabels[$invoice->zatca_status] ?? [$invoice->zatca_status, 'secondary'];
@endphp

<div class="page-header">
    <div>
        <h1>🧾 فاتورة — {{ $invoice->invoice_number }}</h1>
        <p>
            الحالة: <span class="badge badge-{{ $color }}">{{ $label }}</span>
            &nbsp;
            زاتكا: <span class="badge badge-{{ $zcol }}">{{ $zlab }}</span>
        </p>
    </div>
    <div class="header-actions">
        @can('update', $invoice)
            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-secondary">تعديل</a>
        @endcan

        @can('issue', $invoice)
            <form method="POST" action="{{ route('admin.invoices.issue', $invoice) }}" style="display:inline">
                @csrf
                <button class="btn btn-primary" onclick="return confirm('تأكيد إصدار الفاتورة؟ لا يمكن التراجع عن هذا الإجراء.')">
                    📤 إصدار وإرسال لزاتكا
                </button>
            </form>
        @endcan

        @can('sendZatca', $invoice)
            @if ($invoice->zatca_status === 'failed')
                <form method="POST" action="{{ route('admin.invoices.resend-zatca', $invoice) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-warning" onclick="return confirm('إعادة إرسال الفاتورة إلى زاتكا؟')">
                        🔁 إعادة إرسال لزاتكا
                    </button>
                </form>
            @endif
        @endcan

        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-secondary" target="_blank">
            🖨️ PDF
        </a>

        @can('cancel', $invoice)
            <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" style="display:inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" onclick="return confirm('تأكيد إلغاء الفاتورة؟')">
                    ❌ إلغاء
                </button>
            </form>
        @endcan
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
@endif

<div class="card">
    <h3>التفاصيل</h3>
    <table class="table">
        <tr><th>العميل</th><td>{{ $invoice->customer?->name }}</td></tr>
        <tr><th>الرقم الضريبي</th><td>{{ $invoice->customer?->vat_number ?? '—' }}</td></tr>
        <tr><th>اسم الفعالية</th><td>{{ $invoice->event_name }}</td></tr>
        <tr><th>تاريخ البدء</th><td>{{ $invoice->event_start_date?->format('Y-m-d') ?? '—' }}</td></tr>
        <tr><th>تاريخ الانتهاء</th><td>{{ $invoice->event_end_date?->format('Y-m-d') ?? '—' }}</td></tr>
        <tr><th>الموقع</th><td>{{ $invoice->event_location ?? '—' }}</td></tr>
        <tr><th>تاريخ الإصدار</th><td>{{ $invoice->issued_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
        <tr><th>تاريخ الاستحقاق</th><td>{{ $invoice->due_date?->format('Y-m-d') ?? '—' }}</td></tr>
        <tr><th>أنشأها</th><td>{{ $invoice->creator?->name }}</td></tr>
        @if ($invoice->quote)
            <tr><th>عرض السعر المرتبط</th>
                <td><a href="{{ route('admin.quotes.show', $invoice->quote) }}">{{ $invoice->quote->quote_number }}</a></td>
            </tr>
        @endif
        @if ($invoice->notes)
            <tr><th>ملاحظات</th><td>{{ $invoice->notes }}</td></tr>
        @endif
    </table>
</div>

<div class="card">
    <h3>البنود</h3>
    {{-- Mobile: card-style list. Desktop: table. --}}
    <div class="items-mobile">
        @foreach ($invoice->items as $item)
            <div class="item-card">
                <div class="item-card-title">{{ $item->service?->name }}</div>
                @if ($item->description)
                    <div class="item-card-desc">{{ $item->description }}</div>
                @endif
                <div class="item-card-rows">
                    <span>الكمية:</span><strong>{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</strong>
                    <span>سعر الوحدة:</span><strong>{{ number_format((float) $item->unit_price, 2) }}</strong>
                    @if ((float) $item->discount_amount > 0)
                        <span>الخصم:</span><strong>{{ number_format((float) $item->discount_amount, 2) }}</strong>
                    @endif
                    <span>الضريبة {{ rtrim(rtrim((string) $item->tax_rate, '0'), '.') }}%:</span><strong>{{ number_format((float) $item->tax_amount, 2) }}</strong>
                    <span>الإجمالي:</span><strong style="color:#0e7490">{{ number_format((float) $item->total, 2) }}</strong>
                </div>
            </div>
        @endforeach
    </div>
    <div class="table-container items-desktop">
    <table class="table items-table">
        <thead>
            <tr>
                <th>الخدمة</th>
                <th>الوصف</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الخصم</th>
                <th>الضريبة %</th>
                <th>قيمة الضريبة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->service?->name }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->discount_amount, 2) }}</td>
                    <td>{{ rtrim(rtrim((string) $item->tax_rate, '0'), '.') }}</td>
                    <td>{{ number_format((float) $item->tax_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="7" class="text-end">الإجمالي قبل الضريبة:</td>
                <td>{{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
            <tr><td colspan="7" class="text-end">إجمالي الخصومات:</td>
                <td>{{ number_format((float) $invoice->discount_total, 2) }}</td></tr>
            <tr><td colspan="7" class="text-end">إجمالي الضريبة:</td>
                <td>{{ number_format((float) $invoice->tax_total, 2) }}</td></tr>
            <tr><td colspan="7" class="text-end"><strong>الإجمالي النهائي:</strong></td>
                <td><strong>{{ number_format((float) $invoice->grand_total, 2) }}</strong></td></tr>
            <tr><td colspan="7" class="text-end">المدفوع:</td>
                <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td></tr>
        </tfoot>
    </table>
    </div>
</div>

<div class="card">
    <h3>بيانات زاتكا</h3>
    <div class="table-container-auto">
    <table class="table info-table">
        <tr><th>الحالة</th>
            <td><span class="badge badge-{{ $zcol }}">{{ $zlab }}</span></td>
        </tr>
        <tr><th>UUID</th><td><code>{{ $invoice->uuid ?? '—' }}</code></td></tr>
        <tr><th>ICV</th><td>{{ $invoice->icv ?? '—' }}</td></tr>
        <tr><th>PIH</th><td><code style="word-break:break-all">{{ $invoice->pih ?? '—' }}</code></td></tr>
        <tr><th>Invoice Hash</th><td><code style="word-break:break-all">{{ $invoice->invoice_hash ?? '—' }}</code></td></tr>
        <tr><th>تاريخ الإرسال</th><td>{{ $invoice->zatca_submitted_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
        @if ($invoice->qr_code)
            <tr><th>QR Code</th>
                <td><img src="data:image/png;base64,{{ $invoice->qr_code }}" alt="QR Code" style="max-width:160px"></td>
            </tr>
        @endif
        @if (! empty($invoice->zatca_warnings))
            <tr><th>التحذيرات / الأخطاء</th>
                <td>
                    <ul>
                        @foreach ($invoice->zatca_warnings as $w)
                            <li>
                                <strong>{{ $w['code'] ?? 'INFO' }}:</strong>
                                {{ $w['message'] ?? json_encode($w, JSON_UNESCAPED_UNICODE) }}
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endif
    </table>
    </div>
</div>
@push('styles')
<style>
    .items-mobile { display: none; }
    .item-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
    .item-card-title { font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    .item-card-desc { color: #64748b; font-size: 0.85rem; margin-bottom: 8px; }
    .item-card-rows { display: grid; grid-template-columns: auto 1fr; gap: 4px 12px; font-size: 0.85rem; }
    .item-card-rows span { color: #64748b; }
    .item-card-rows strong { text-align: end; }
    @media (max-width: 768px) {
        .items-mobile { display: block; }
        .items-desktop { display: none; }
    }
</style>
@endpush
@endsection
