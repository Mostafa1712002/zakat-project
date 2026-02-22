@extends('layouts.app')

@section('title', 'عرض فاتورة البيع')

@section('content')
<div class="invoice-wrapper">
    <!-- Action Buttons - Screen Only -->
    <div class="invoice-actions no-print">
        <a href="{{ route('sales.pdf', $sale) }}" class="btn btn-primary btn-lg">
            <span class="btn-icon">🖨️</span>
            طباعة
        </a>
        @if($sale->status === 'draft')
            <form action="{{ route('sales.confirm', $sale) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من تأكيد الفاتورة؟ سيتم خصم الكميات من المخزون.')">
                @csrf
                <button type="submit" class="btn btn-success btn-lg">
                    <span class="btn-icon">✅</span>
                    تأكيد الفاتورة
                </button>
            </form>
            <a href="{{ route('sales.edit', $sale) }}" class="btn">تعديل</a>
        @endif
        <a href="{{ route('sales.index') }}" class="btn">← رجوع للمبيعات</a>
    </div>

    <!-- Invoice Container -->
    <div class="invoice-container">
        <div class="print-minimal">
            <div class="print-minimal-header">
                <div class="print-logo-block">
                    @if(!empty($companyLogo))
                        <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $companyName ?: 'الشركة' }}" class="print-logo">
                    @else
                        <img src="{{ asset('logo.png') }}" alt="{{ $companyName ?: 'الشركة' }}" class="print-logo">
                    @endif
                </div>
                <div class="print-invoice-meta">
                    <div class="print-invoice-label">فاتورة مبيعات</div>
                    <div class="print-invoice-number">{{ $sale->invoice_number }}</div>
                </div>
            </div>

            <div class="print-minimal-row">
                <div class="print-info-cell">
                    <span class="print-info-label">هاتف مشرف الخط</span>
                    <span class="print-info-value ltr">{{ !empty($supervisorPhone) ? $supervisorPhone : '-' }}</span>
                </div>
                <div class="print-info-cell">
                    <span class="print-info-label">هاتف المندوب</span>
                    <span class="print-info-value ltr">{{ $sale->salesRep->phone ?? '-' }}</span>
                </div>
            </div>

            <div class="print-minimal-row">
                <div class="print-info-cell">
                    <span class="print-info-label">بيانات العميل</span>
                    <span class="print-info-value">{{ $sale->customer->name ?? '-' }} {{ $sale->customer->phone ? '- ' . $sale->customer->phone : '' }}</span>
                </div>
                <div class="print-info-cell">
                    <span class="print-info-label">تاريخ الفاتورة</span>
                    <span class="print-info-value ltr">{{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</span>
                </div>
            </div>

            <table class="print-items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الخدمة</th>
                        <th>الإجمالي</th>
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

            <div class="print-payment-summary">
                <div class="print-total-box">
                    <span class="print-total-label">المدفوع</span>
                    <span class="print-total-value">{{ number_format($sale->paid_amount, 2) }} ج.م</span>
                </div>
                <div class="print-total-box">
                    <span class="print-total-label">المتبقي</span>
                    <span class="print-total-value">{{ number_format($sale->remaining_amount, 2) }} ج.م</span>
                </div>
            </div>

            <div class="print-signatures">
                <div class="print-signature-box">
                    <span class="print-signature-label">توقيع العميل</span>
                    <div class="print-signature-line"></div>
                </div>
                @if(!empty($companyStamp))
                <div class="print-stamp-box">
                    <img src="{{ asset('storage/' . $companyStamp) }}" alt="ختم الشركة" class="print-stamp-image">
                </div>
                @endif
                <div class="print-signature-box">
                    <span class="print-signature-label">توقيع المندوب</span>
                    <div class="print-signature-line"></div>
                </div>
            </div>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1 class="company-name">{{ $companyName ?: 'الشركة' }}</h1>
            </div>
            <div class="invoice-title">
                <div class="invoice-title-main">
                    <h2>فاتورة مبيعات</h2>
                    <div class="invoice-number">{{ $sale->invoice_number }}</div>
                </div>
                <div class="invoice-status no-print">
                    @switch($sale->status)
                        @case('draft') مسودة @break
                        @case('confirmed') مؤكدة @break
                        @case('delivered') تم التسليم @break
                        @case('cancelled') ملغاة @break
                    @endswitch
                </div>
            </div>
        </div>

        <!-- Contact Numbers Bar -->
        <div class="contacts-bar">
            @if(!empty($supervisorPhone))
            <div class="contact-item">
                <span class="contact-label">مشرف الخط:</span>
                <span class="contact-value">{{ $supervisorPhone }}</span>
            </div>
            @endif
            @if($sale->salesRep)
            <div class="contact-item">
                <span class="contact-label">المندوب:</span>
                <span class="contact-value">{{ $sale->salesRep->name }} {{ $sale->salesRep->phone ? '- ' . $sale->salesRep->phone : '' }}</span>
            </div>
            @endif
        </div>

        <!-- Print: Compact Info Row -->
        <div class="print-info-row">
            <div><strong>العميل:</strong> {{ $sale->customer->name ?? '-' }} {{ $sale->customer->phone ? '- ' . $sale->customer->phone : '' }}</div>
            <div><strong>التاريخ:</strong> {{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</div>
        </div>

        <!-- Info Grid - Screen only details -->
        <div class="info-grid no-print">
            <div class="info-box">
                <div class="info-box-header">
                    <span class="info-icon">👤</span>
                    <h4>بيانات العميل</h4>
                </div>
                <div class="info-box-body overflow-auto">
                    <table class="info-table text-nowrap">
                        <tr>
                            <td class="info-label">الاسم:</td>
                            <td class="info-value"><strong>{{ $sale->customer->name ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">الهاتف:</td>
                            <td class="info-value">{{ $sale->customer->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">العنوان:</td>
                            <td class="info-value">{{ $sale->customer->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">المدينة:</td>
                            <td class="info-value">{{ $sale->customer->city ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="info-box">
                <div class="info-box-header">
                    <span class="info-icon">📋</span>
                    <h4>بيانات الفاتورة</h4>
                </div>
                <div class="info-box-body overflow-auto">
                    <table class="info-table text-nowrap">
                        <tr>
                            <td class="info-label">التاريخ:</td>
                            <td class="info-value"><strong>{{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">الاستحقاق:</td>
                            <td class="info-value">{{ $sale->due_date?->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">نوع الدفع:</td>
                            <td class="info-value">{{ $sale->payment_type === 'credit' ? 'آجل' : 'نقدي' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">حالة الدفع:</td>
                            <td class="info-value">
                                @switch($sale->payment_status)
                                    @case('unpaid') غير مدفوعة @break
                                    @case('partial') جزئي @break
                                    @case('paid') مدفوعة @break
                                    @case('overdue') متأخرة @break
                                @endswitch
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-card">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الخدمة</th>
                        <th class="no-print">الضريبة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="item-name">{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->discount_amount, 2) }}</td>
                        <td class="no-print">{{ number_format($item->tax_amount, 2) }}</td>
                        <td class="item-total">{{ number_format($item->total ?? $item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="subtotal-row">
                        <td colspan="5" class="text-left"><strong>الإجمالي الفرعي</strong></td>
                        <td class="no-print"></td>
                        <td><strong>{{ number_format($sale->subtotal, 2) }} ج.م</strong></td>
                    </tr>
                    @if($sale->discount_amount > 0)
                    <tr>
                        <td colspan="5" class="text-left">الخدمة</td>
                        <td class="no-print"></td>
                        <td class="discount">- {{ number_format($sale->discount_amount, 2) }} ج.م</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td colspan="5" class="text-left"><strong>الإجمالي النهائي</strong></td>
                        <td class="no-print"></td>
                        <td class="grand-total"><strong>{{ number_format($sale->total_amount, 2) }} ج.م</strong></td>
                    </tr>
                    @if($sale->paid_amount > 0)
                    <tr>
                        <td colspan="5" class="text-left">المدفوع</td>
                        <td class="no-print"></td>
                        <td class="paid">{{ number_format($sale->paid_amount, 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-left"><strong>المتبقي</strong></td>
                        <td class="no-print"></td>
                        <td class="remaining"><strong>{{ number_format($sale->remaining_amount, 2) }} ج.م</strong></td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        <!-- Signatures Section -->
        <div class="signatures-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <span>توقيع العميل</span>
            </div>
            @if(!empty($companyStamp))
            <div class="stamp-box">
                <img src="{{ asset('storage/' . $companyStamp) }}" alt="ختم الشركة" class="stamp-image">
            </div>
            @endif
            <div class="signature-box">
                <div class="signature-line"></div>
                <span>توقيع المندوب</span>
            </div>
        </div>
    </div>

    {{-- Screen-only sections --}}
    @if($sale->payments->count() > 0)
    <div class="card no-print" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3>الدفعات</h3>
            <div class="table-container overflow-auto">
                <table class="table text-nowrap">
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
    <div class="card no-print" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3>ملاحظات</h3>
            <p>{{ $sale->notes }}</p>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* Invoice Wrapper */
.invoice-wrapper {
    max-width: 980px;
    margin: 0 auto;
    padding: 20px;
}

/* Action Buttons */
.invoice-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.invoice-actions .btn-lg {
    padding: 12px 24px;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-icon { font-size: 1.2rem; }

/* Invoice Container */
.invoice-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 30px;
    border: 1px solid #e5e7eb;
}

.print-minimal {
    display: none;
}

/* Invoice Header */
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding-bottom: 16px;
    border-bottom: 3px double var(--primary);
    margin-bottom: 16px;
}

.company-info {
    flex: 1;
    text-align: right;
}

.company-name {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--primary);
    margin: 0;
}

.invoice-title {
    flex: 1;
    text-align: left;
}

.invoice-title-main {
    display: inline-flex;
    align-items: baseline;
    gap: 8px;
    white-space: nowrap;
}

.invoice-title h2 {
    font-size: 1.3rem;
    color: #374151;
    margin: 0;
}

.invoice-number {
    font-size: 1.4rem;
    font-weight: bold;
    color: var(--primary);
    margin-top: 0;
}

.invoice-status { margin-top: 8px; }

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-draft { background: #f3f4f6; color: #6b7280; }
.status-confirmed { background: #dbeafe; color: #1d4ed8; }
.status-delivered { background: #d1fae5; color: #059669; }
.status-cancelled { background: #fee2e2; color: #dc2626; }

.btn-success {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    border: none;
}
.btn-success:hover {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

/* Contacts Bar */
.contacts-bar {
    display: flex;
    justify-content: center;
    gap: 40px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.contact-label {
    font-weight: 600;
    color: #374151;
}

.contact-value {
    color: var(--primary);
    font-weight: bold;
    direction: ltr;
}

/* Print Info Row - hidden on screen, shown in print */
.print-info-row {
    display: none;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.info-box {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}

.info-box-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e5e7eb;
}

.info-box-header h4 {
    margin: 0;
    font-size: 0.95rem;
    color: var(--primary);
}

.info-icon { font-size: 1.1rem; }

.info-box-body { padding: 12px 16px; }

.info-table { width: 100%; }

.info-table tr td {
    padding: 6px 0;
    vertical-align: top;
}

.info-label {
    color: #6b7280;
    width: 35%;
    font-size: 0.9rem;
}

.info-value {
    color: #111827;
    font-size: 0.9rem;
}

.payment-type {
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.payment-type.cash { background: #d1fae5; color: #059669; }
.payment-type.credit { background: #fef3c7; color: #d97706; }

.payment-badge {
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.payment-badge.unpaid { background: #fee2e2; color: #dc2626; }
.payment-badge.partial { background: #fef3c7; color: #d97706; }
.payment-badge.paid { background: #d1fae5; color: #059669; }
.payment-badge.overdue { background: #ffedd5; color: #ea580c; }

/* Items Table */
.items-card {
    margin-bottom: 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th {
    background: var(--primary);
    color: white;
    padding: 10px 8px;
    font-size: 0.9rem;
    text-align: center;
    font-weight: 600;
}

.items-table th:first-child { border-radius: 8px 0 0 0; }
.items-table th:last-child { border-radius: 0 8px 0 0; }

.items-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #e5e7eb;
    text-align: center;
    font-size: 0.9rem;
}

.items-table tbody tr:hover { background: #f9fafb; }

.item-name {
    text-align: right !important;
    font-weight: 500;
}

.item-total {
    font-weight: 600;
    color: var(--primary);
}

.items-table tfoot td {
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
}

.discount { color: #dc2626; }
.paid { color: #059669; }
.remaining { color: #d97706; }

.grand-total-row {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

.grand-total-row td {
    border-top: 2px solid var(--primary) !important;
    padding: 12px 8px !important;
}

.grand-total {
    font-size: 1.1rem;
    color: var(--primary);
}

/* Signatures Section */
.signatures-section {
    display: flex;
    justify-content: space-between;
    gap: 24px;
    margin: 28px 0 10px;
    padding-top: 14px;
    border-top: 1px dashed #d1d5db;
}

.signature-box {
    text-align: center;
    flex: 1;
    max-width: 45%;
}

.signature-line {
    border-bottom: 1px solid #374151;
    height: 50px;
    margin-bottom: 8px;
}

.signature-box span {
    font-size: 0.85rem;
    color: #6b7280;
}

/* Stamp */
.stamp-box {
    display: flex;
    align-items: center;
    justify-content: center;
}
.stamp-image {
    max-width: 100px;
    max-height: 100px;
    object-fit: contain;
    opacity: 0.85;
}

/* ========== PRINT STYLES ========== */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    html,
    body {
        width: 210mm;
        height: auto;
        background: white !important;
        margin: 0;
        padding: 0;
        font-size: 10pt;
        line-height: 1.3;
        font-family: 'Arial', 'Tahoma', sans-serif;
    }

    .no-print,
    .sidebar,
    nav,
    header,
    footer,
    .invoice-actions,
    .menu-toggle,
    .print-minimal,
    .contacts-bar,
    .print-info-row {
        display: none !important;
    }

    .main {
        margin-right: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: visible !important;
    }

    .app {
        overflow: visible !important;
    }

    .invoice-wrapper {
        max-width: 100%;
        padding: 0;
        margin: 0;
    }

    .invoice-container {
        box-shadow: none;
        border: none;
        padding: 8mm 10mm;
        border-radius: 0;
    }

    /* Show screen layout in print */
    .invoice-header {
        display: flex !important;
        padding-bottom: 10px;
        margin-bottom: 14px;
    }

    .company-name {
        font-size: 16pt;
    }

    .invoice-title h2 {
        font-size: 12pt;
    }

    .invoice-number {
        font-size: 13pt;
    }

    .info-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 16px;
    }

    .info-box {
        border: 1px solid #d1d5db;
    }

    .info-box-header {
        padding: 8px 12px;
        background: #f1f5f9 !important;
    }

    .info-box-header h4 {
        font-size: 9pt;
    }

    .info-box-body {
        padding: 8px 12px;
    }

    .info-table tr td {
        padding: 3px 0;
        font-size: 9pt;
    }

    .items-card {
        display: block !important;
    }

    .items-table th {
        background: #0891b2 !important;
        color: white !important;
        padding: 7px 6px;
        font-size: 9pt;
    }

    .items-table td {
        padding: 6px;
        font-size: 9pt;
    }

    .grand-total-row {
        background: #f0f9ff !important;
    }

    .grand-total-row td {
        border-top: 2px solid #0891b2 !important;
    }

    .signatures-section {
        display: flex !important;
        margin-top: 20px;
    }

    .signature-line {
        height: 40px;
    }

    .signature-box span {
        font-size: 9pt;
    }

    .stamp-image {
        max-width: 70px;
        max-height: 70px;
    }

    @page {
        size: A4 portrait;
        margin: 8mm;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .invoice-header {
        align-items: flex-start;
        gap: 12px;
    }

    .company-name {
        font-size: 1.35rem;
    }

    .invoice-title h2 {
        font-size: 1.05rem;
    }

    .invoice-number {
        font-size: 1.1rem;
    }

    .invoice-title-main {
        flex-wrap: wrap;
        white-space: normal;
    }

    .contacts-bar {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }

    .info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .info-box-body {
        padding: 10px 12px;
    }

    .signatures-section {
        flex-direction: row;
        gap: 12px;
    }

    .signature-box {
        max-width: none;
    }

    .print-minimal-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 560px) {
    .company-name {
        font-size: 1.15rem;
    }

    .company-slogan {
        font-size: 0.78rem;
    }

    .invoice-title h2 {
        font-size: 0.9rem;
    }

    .invoice-number {
        font-size: 0.95rem;
    }

    .info-label,
    .info-value {
        font-size: 0.8rem;
    }

    .signature-line {
        height: 36px;
    }
}
</style>
@endpush
