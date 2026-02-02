@extends('layouts.app')

@section('title', 'عرض فاتورة البيع')

@section('content')
<!-- Print Header - Only visible when printing -->
<div class="print-only print-invoice">
    <div class="print-header">
        <div class="company-info">
            <h1 class="company-name">{{ config('app.name', 'شركة روجينس') }}</h1>
            <p class="company-slogan">للتجارة والتوزيع</p>
        </div>
        <div class="invoice-title">
            <h2>فاتورة مبيعات</h2>
            <div class="invoice-number">{{ $sale->invoice_number }}</div>
        </div>
    </div>

    <div class="print-contacts-bar">
        <div class="contact-item">
            <span class="contact-label">مشرف الخط:</span>
            <span class="contact-value">01000000000</span>
        </div>
        @if($sale->salesRep && $sale->salesRep->phone)
        <div class="contact-item">
            <span class="contact-label">رقم المندوب:</span>
            <span class="contact-value">{{ $sale->salesRep->phone }}</span>
        </div>
        @endif
    </div>

    <div class="print-info-grid">
        <div class="info-box customer-box">
            <h4>بيانات العميل</h4>
            <table class="info-table">
                <tr><td>الاسم:</td><td><strong>{{ $sale->customer->name ?? '-' }}</strong></td></tr>
                <tr><td>الهاتف:</td><td>{{ $sale->customer->phone ?? '-' }}</td></tr>
                <tr><td>العنوان:</td><td>{{ $sale->customer->address ?? '-' }}</td></tr>
                <tr><td>المدينة:</td><td>{{ $sale->customer->city ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="info-box invoice-box">
            <h4>بيانات الفاتورة</h4>
            <table class="info-table">
                <tr><td>التاريخ:</td><td><strong>{{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</strong></td></tr>
                <tr><td>الاستحقاق:</td><td>{{ $sale->due_date?->format('Y-m-d') ?? '-' }}</td></tr>
                <tr><td>نوع الدفع:</td><td>{{ $sale->payment_type === 'credit' ? 'آجل' : 'نقدي' }}</td></tr>
                <tr><td>المندوب:</td><td>{{ $sale->salesRep->name ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="print-items">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">الصنف</th>
                    <th style="width: 12%;">الكمية</th>
                    <th style="width: 15%;">السعر</th>
                    <th style="width: 12%;">الخصم</th>
                    <th style="width: 16%;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->discount_amount, 2) }}</td>
                    <td>{{ number_format($item->total ?? $item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="print-totals">
        <table class="totals-table">
            <tr>
                <td>الإجمالي الفرعي:</td>
                <td>{{ number_format($sale->subtotal, 2) }} ج.م</td>
            </tr>
            @if($sale->discount_amount > 0)
            <tr>
                <td>الخدمة:</td>
                <td>- {{ number_format($sale->discount_amount, 2) }} ج.م</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>الإجمالي النهائي:</strong></td>
                <td><strong>{{ number_format($sale->total_amount, 2) }} ج.م</strong></td>
            </tr>
            @if($sale->paid_amount > 0)
            <tr>
                <td>المدفوع:</td>
                <td>{{ number_format($sale->paid_amount, 2) }} ج.م</td>
            </tr>
            <tr>
                <td>المتبقي:</td>
                <td>{{ number_format($sale->remaining_amount, 2) }} ج.م</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="print-footer">
        <div class="footer-signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <span>توقيع العميل</span>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <span>توقيع المندوب</span>
            </div>
        </div>
        <div class="footer-note">
            <p>شكراً لتعاملكم معنا</p>
            <p class="small">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>
</div>

<!-- Screen View -->
<div class="screen-only">
    <div class="page-header">
        <div>
            <h1>💰 {{ $sale->invoice_number }}</h1>
            <p>تفاصيل فاتورة البيع</p>
        </div>
        <div class="header-actions">
            <button onclick="window.print()" class="btn btn-primary">🖨️ طباعة الفاتورة</button>
            @if($sale->status === 'draft')
                <a href="{{ route('sales.edit', $sale) }}" class="btn">تعديل</a>
            @endif
            <a href="{{ route('sales.index') }}" class="btn">← رجوع</a>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3>👤 بيانات العميل</h3>
                <p><strong>الاسم:</strong> {{ $sale->customer->name ?? '-' }}</p>
                <p><strong>الهاتف:</strong> {{ $sale->customer->phone ?? '-' }}</p>
                <p><strong>العنوان:</strong> {{ $sale->customer->address ?? '-' }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>📋 بيانات الفاتورة</h3>
                <p><strong>التاريخ:</strong> {{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</p>
                <p><strong>تاريخ الاستحقاق:</strong> {{ $sale->due_date?->format('Y-m-d') ?? '-' }}</p>
                <p><strong>الفرع:</strong> {{ $sale->branch->name ?? '-' }}</p>
                <p><strong>المستودع:</strong> {{ $sale->warehouse->name ?? '-' }}</p>
                <p><strong>المندوب:</strong> {{ $sale->salesRep->name ?? '-' }} {{ $sale->salesRep->phone ? '(' . $sale->salesRep->phone . ')' : '' }}</p>
                <p><strong>نوع الدفع:</strong> {{ $sale->payment_type === 'credit' ? 'آجل' : 'نقدي' }}</p>
                <p><strong>حالة الفاتورة:</strong>
                    @switch($sale->status)
                        @case('draft')<span class="badge text-muted">مسودة</span>@break
                        @case('confirmed')<span class="badge badge-primary">مؤكدة</span>@break
                        @case('delivered')<span class="badge badge-success">تم التسليم</span>@break
                        @case('cancelled')<span class="badge badge-danger">ملغاة</span>@break
                        @default<span class="badge text-muted">{{ $sale->status }}</span>
                    @endswitch
                </p>
                <p><strong>حالة الدفع:</strong>
                    @switch($sale->payment_status)
                        @case('unpaid')<span class="badge badge-danger">غير مدفوعة</span>@break
                        @case('partial')<span class="badge badge-warning">جزئي</span>@break
                        @case('paid')<span class="badge badge-success">مدفوعة</span>@break
                        @case('overdue')<span class="badge badge-warning">متأخرة</span>@break
                        @default<span class="badge text-muted">{{ $sale->payment_status }}</span>
                    @endswitch
                </p>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الخصم</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                        <td>{{ number_format($item->discount_amount, 2) }} ج.م</td>
                        <td>{{ number_format($item->total ?? $item->subtotal, 2) }} ج.م</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-left"><strong>الإجمالي الفرعي</strong></td>
                        <td><strong>{{ number_format($sale->subtotal, 2) }} ج.م</strong></td>
                    </tr>
                    @if($sale->discount_amount > 0)
                    <tr>
                        <td colspan="5" class="text-left">الخدمة</td>
                        <td>- {{ number_format($sale->discount_amount, 2) }} ج.م</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="5" class="text-left"><strong>الإجمالي النهائي</strong></td>
                        <td><strong style="color: var(--primary); font-size: 1.25rem;">{{ number_format($sale->total_amount, 2) }} ج.م</strong></td>
                    </tr>
                    @if($sale->paid_amount > 0)
                    <tr>
                        <td colspan="5" class="text-left">المدفوع</td>
                        <td>{{ number_format($sale->paid_amount, 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-left">المتبقي</td>
                        <td>{{ number_format($sale->remaining_amount, 2) }} ج.م</td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>

    @if($sale->payments->count() > 0)
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3>💳 الدفعات</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>الطريقة</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->payments as $index => $payment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $payment->paid_at?->format('Y-m-d') ?? $payment->created_at->format('Y-m-d') }}</td>
                            <td>{{ number_format($payment->amount, 2) }} ج.م</td>
                            <td>{{ $payment->method ?? '-' }}</td>
                            <td>{{ $payment->status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($sale->notes)
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3>📝 ملاحظات</h3>
            <p>{{ $sale->notes }}</p>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* Screen Styles */
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
.screen-only { display: block; }
.print-only { display: none; }

/* Print Styles */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        background: white !important;
        margin: 0;
        padding: 0;
        font-family: 'Arial', 'Tahoma', sans-serif;
        font-size: 11pt;
        color: #000;
        direction: rtl;
    }

    .screen-only,
    .sidebar,
    nav,
    header,
    footer,
    .no-print {
        display: none !important;
    }

    .print-only {
        display: block !important;
    }

    .print-invoice {
        width: 100%;
        max-width: 210mm;
        margin: 0 auto;
        padding: 10mm;
    }

    /* Header */
    .print-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 10px;
        border-bottom: 3px double #000;
        margin-bottom: 10px;
    }

    .company-info {
        text-align: right;
    }

    .company-name {
        font-size: 20pt;
        font-weight: bold;
        margin: 0;
        color: #0891b2;
    }

    .company-slogan {
        font-size: 10pt;
        color: #666;
        margin: 2px 0 0 0;
    }

    .invoice-title {
        text-align: left;
    }

    .invoice-title h2 {
        font-size: 16pt;
        margin: 0;
        color: #333;
    }

    .invoice-number {
        font-size: 14pt;
        font-weight: bold;
        color: #0891b2;
        margin-top: 5px;
    }

    /* Contacts Bar */
    .print-contacts-bar {
        display: flex;
        justify-content: center;
        gap: 40px;
        background: #f5f5f5;
        padding: 8px 15px;
        margin-bottom: 15px;
        border-radius: 5px;
    }

    .contact-item {
        display: flex;
        gap: 5px;
    }

    .contact-label {
        font-weight: bold;
        color: #333;
    }

    .contact-value {
        color: #0891b2;
        font-weight: bold;
        direction: ltr;
    }

    /* Info Grid */
    .print-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }

    .info-box {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
    }

    .info-box h4 {
        font-size: 11pt;
        color: #0891b2;
        margin: 0 0 8px 0;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }

    .info-table {
        width: 100%;
        font-size: 10pt;
    }

    .info-table td {
        padding: 3px 0;
        vertical-align: top;
    }

    .info-table td:first-child {
        color: #666;
        width: 35%;
    }

    /* Items Table */
    .print-items {
        margin-bottom: 15px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table th {
        background: #0891b2 !important;
        color: white !important;
        padding: 10px 8px;
        font-size: 10pt;
        text-align: center;
        border: 1px solid #0891b2;
    }

    .items-table td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
        font-size: 10pt;
    }

    .items-table tbody tr:nth-child(even) {
        background: #f9f9f9;
    }

    /* Totals */
    .print-totals {
        display: flex;
        justify-content: flex-start;
        margin-bottom: 20px;
    }

    .totals-table {
        width: 250px;
        border-collapse: collapse;
    }

    .totals-table td {
        padding: 6px 10px;
        font-size: 10pt;
        border-bottom: 1px solid #eee;
    }

    .totals-table td:first-child {
        text-align: right;
        color: #666;
    }

    .totals-table td:last-child {
        text-align: left;
        font-weight: 500;
        direction: ltr;
    }

    .total-row {
        background: #f0f9ff !important;
        border-top: 2px solid #0891b2 !important;
    }

    .total-row td {
        font-size: 12pt !important;
        padding: 10px !important;
        border-bottom: none !important;
    }

    /* Footer */
    .print-footer {
        margin-top: 30px;
        padding-top: 15px;
        border-top: 1px dashed #ccc;
    }

    .footer-signatures {
        display: flex;
        justify-content: space-around;
        margin-bottom: 20px;
    }

    .signature-box {
        text-align: center;
        width: 150px;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        height: 40px;
        margin-bottom: 5px;
    }

    .signature-box span {
        font-size: 9pt;
        color: #666;
    }

    .footer-note {
        text-align: center;
        color: #666;
    }

    .footer-note p {
        margin: 3px 0;
    }

    .footer-note .small {
        font-size: 8pt;
    }

    /* Page Settings */
    @page {
        size: A4;
        margin: 10mm;
    }
}
</style>
@endpush
