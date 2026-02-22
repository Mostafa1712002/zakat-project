@extends('layouts.app')

@section('title', 'عرض فاتورة الشراء')

@section('content')
<div class="invoice-wrapper">
    <!-- Action Buttons - Screen Only -->
    <div class="invoice-actions no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <span class="btn-icon">🖨️</span>
            طباعة الفاتورة
        </button>
        <a href="{{ route('purchases.pdf', $purchase) }}" class="btn btn-lg" style="background: linear-gradient(135deg, #dc2626, #b91c1c); color: white;">
            <span class="btn-icon">📄</span>
            تحميل PDF
        </a>
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn">تعديل</a>
        <a href="{{ route('purchases.index') }}" class="btn">← رجوع للمشتريات</a>
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
                    <div class="print-invoice-label">فاتورة مشتريات</div>
                    <div class="print-invoice-number">{{ $purchase->invoice_number }}</div>
                </div>
            </div>

            <div class="print-minimal-row">
                <div class="print-info-cell">
                    <span class="print-info-label">بيانات المورد</span>
                    <span class="print-info-value">{{ $purchase->supplier->name ?? '-' }} {{ $purchase->supplier->phone ? '- ' . $purchase->supplier->phone : '' }}</span>
                </div>
                <div class="print-info-cell">
                    <span class="print-info-label">تاريخ الفاتورة</span>
                    <span class="print-info-value ltr">{{ $purchase->invoice_date?->format('Y-m-d') ?? '-' }}</span>
                </div>
            </div>

            <div class="print-minimal-row">
                <div class="print-info-cell">
                    <span class="print-info-label">المستودع</span>
                    <span class="print-info-value">{{ $purchase->warehouse->name ?? '-' }}</span>
                </div>
                <div class="print-info-cell">
                    <span class="print-info-label">رقم فاتورة المورد</span>
                    <span class="print-info-value ltr">{{ $purchase->supplier_invoice_number ?? '-' }}</span>
                </div>
            </div>

            <table class="print-items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_cost ?? $item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="print-payment-summary">
                <div class="print-total-box">
                    <span class="print-total-label">المدفوع</span>
                    <span class="print-total-value">{{ number_format($purchase->paid_amount, 2) }} ج.م</span>
                </div>
                <div class="print-total-box">
                    <span class="print-total-label">المتبقي</span>
                    <span class="print-total-value">{{ number_format($purchase->remaining_amount, 2) }} ج.م</span>
                </div>
            </div>

            <div class="print-signatures">
                <div class="print-signature-box">
                    <span class="print-signature-label">توقيع المورد</span>
                    <div class="print-signature-line"></div>
                </div>
                @if(!empty($companyStamp))
                <div class="print-stamp-box">
                    <img src="{{ asset('storage/' . $companyStamp) }}" alt="ختم الشركة" class="print-stamp-image">
                </div>
                @endif
                <div class="print-signature-box">
                    <span class="print-signature-label">توقيع المستلم</span>
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
                    <h2>فاتورة مشتريات</h2>
                    <div class="invoice-number">{{ $purchase->invoice_number }}</div>
                </div>
                <div class="invoice-status no-print">
                    @switch($purchase->status)
                        @case('draft')<span class="status-badge status-draft">مسودة</span>@break
                        @case('ordered')<span class="status-badge status-ordered">تم الطلب</span>@break
                        @case('received')<span class="status-badge status-received">مستلم</span>@break
                        @case('cancelled')<span class="status-badge status-cancelled">ملغي</span>@break
                    @endswitch
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid no-print">
            <div class="info-box">
                <div class="info-box-header">
                    <span class="info-icon">🏢</span>
                    <h4>بيانات المورد</h4>
                </div>
                <div class="info-box-body overflow-auto">
                    <table class="info-table text-nowrap">
                        <tr>
                            <td class="info-label">الاسم:</td>
                            <td class="info-value"><strong>{{ $purchase->supplier->name ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">الهاتف:</td>
                            <td class="info-value">{{ $purchase->supplier->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">العنوان:</td>
                            <td class="info-value">{{ $purchase->supplier->address ?? '-' }}</td>
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
                            <td class="info-value"><strong>{{ $purchase->invoice_date?->format('Y-m-d') ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">الاستحقاق:</td>
                            <td class="info-value">{{ $purchase->due_date?->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">المستودع:</td>
                            <td class="info-value">{{ $purchase->warehouse->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">رقم فاتورة المورد:</td>
                            <td class="info-value">{{ $purchase->supplier_invoice_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">نوع الدفع:</td>
                            <td class="info-value">
                                <span class="payment-type {{ ($purchase->payment_type ?? 'credit') === 'credit' ? 'credit' : 'cash' }}">
                                    {{ ($purchase->payment_type ?? 'credit') === 'credit' ? 'آجل' : 'نقدي' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">حالة الدفع:</td>
                            <td class="info-value">
                                @switch($purchase->payment_status)
                                    @case('unpaid')<span class="payment-badge unpaid">غير مدفوعة</span>@break
                                    @case('partial')<span class="payment-badge partial">جزئي</span>@break
                                    @case('paid')<span class="payment-badge paid">مدفوعة</span>@break
                                    @case('overdue')<span class="payment-badge overdue">متأخرة</span>@break
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
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="item-name">{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_cost ?? $item->unit_price, 2) }} ج.م</td>
                        <td class="item-total">{{ number_format($item->subtotal, 2) }} ج.م</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="subtotal-row">
                        <td colspan="4" class="text-left"><strong>الإجمالي الفرعي</strong></td>
                        <td><strong>{{ number_format($purchase->subtotal, 2) }} ج.م</strong></td>
                    </tr>
                    @if($purchase->discount_amount > 0)
                    <tr>
                        <td colspan="4" class="text-left">الخصم</td>
                        <td class="discount">- {{ number_format($purchase->discount_amount, 2) }} ج.م</td>
                    </tr>
                    @endif
                    @if($purchase->shipping_amount > 0)
                    <tr>
                        <td colspan="4" class="text-left">الشحن</td>
                        <td>{{ number_format($purchase->shipping_amount, 2) }} ج.م</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td colspan="4" class="text-left"><strong>الإجمالي النهائي</strong></td>
                        <td class="grand-total"><strong>{{ number_format($purchase->total_amount, 2) }} ج.م</strong></td>
                    </tr>
                    @if($purchase->paid_amount > 0)
                    <tr>
                        <td colspan="4" class="text-left">المدفوع</td>
                        <td class="paid">{{ number_format($purchase->paid_amount, 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-left"><strong>المتبقي</strong></td>
                        <td class="remaining"><strong>{{ number_format($purchase->remaining_amount, 2) }} ج.م</strong></td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        <!-- Signatures Section -->
        <div class="signatures-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <span>توقيع المورد</span>
            </div>
            @if(!empty($companyStamp))
            <div class="stamp-box">
                <img src="{{ asset('storage/' . $companyStamp) }}" alt="ختم الشركة" class="stamp-image">
            </div>
            @endif
            <div class="signature-box">
                <div class="signature-line"></div>
                <span>توقيع المستلم</span>
            </div>
        </div>
    </div>

    @if($purchase->notes)
    <div class="card no-print" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3>ملاحظات</h3>
            <p>{{ $purchase->notes }}</p>
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
.status-ordered { background: #dbeafe; color: #1d4ed8; }
.status-received { background: #d1fae5; color: #059669; }
.status-cancelled { background: #fee2e2; color: #dc2626; }

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
    width: 40%;
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
    }

    body {
        background: white !important;
        margin: 0;
        padding: 0;
        font-size: 8.5pt;
        line-height: 1.2;
        font-family: 'Arial', 'Tahoma', sans-serif;
    }

    .no-print,
    .sidebar,
    nav,
    header,
    footer,
    .invoice-actions,
    .menu-toggle {
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
        padding: 5mm 6mm 4mm;
        border-radius: 0;
    }

    .invoice-header,
    .info-grid,
    .items-card,
    .signatures-section {
        display: none !important;
    }

    .print-minimal {
        display: block !important;
        color: #111827;
        page-break-inside: avoid;
        break-inside: avoid;
        border: 1px solid #d9e2ec;
        border-radius: 4px;
        padding: 2.4mm 2.6mm 2.2mm;
        background: #ffffff;
    }

    .print-minimal-header {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 1.4mm;
        margin-bottom: 1.8mm;
        padding-bottom: 1.6mm;
        border-bottom: 1px solid #0f172a;
    }

    .print-logo-block {
        width: 100%;
        text-align: center;
    }

    .print-logo {
        width: 22mm;
        max-height: 12mm;
        object-fit: contain;
    }

    .print-invoice-meta {
        width: 100%;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .print-invoice-label {
        font-size: 9pt;
        font-weight: 700;
        margin-bottom: 0.8mm;
        color: #0f172a;
    }

    .print-invoice-number {
        font-size: 9pt;
        font-weight: 700;
        letter-spacing: 0.1px;
        direction: ltr;
        padding: 0.6mm 1.6mm;
        border: 1px solid #94a3b8;
        border-radius: 3px;
        background: #f8fafc;
    }

    .print-minimal-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 2.2mm;
        margin-bottom: 1.4mm;
    }

    .print-info-cell {
        border: 1px solid #d5dee8;
        border-radius: 3px;
        background: #f8fafc;
        padding: 1.2mm 1.6mm;
        min-height: 10mm;
    }

    .print-info-label {
        display: block;
        font-size: 7.2pt;
        color: #475569;
        margin-bottom: 0.7mm;
        font-weight: 700;
    }

    .print-info-value {
        display: block;
        font-size: 8pt;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        word-break: break-word;
    }

    .print-info-value.ltr {
        direction: ltr;
        text-align: left;
    }

    .print-items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 1.4mm 0 1.5mm;
    }

    .print-items-table th,
    .print-items-table td {
        border: 1px solid #cfd8e3;
        padding: 0.9mm 0.8mm;
        font-size: 6.9pt;
        text-align: center;
        white-space: normal;
        word-break: break-word;
        line-height: 1.15;
    }

    .print-items-table th {
        background: #e2e8f0 !important;
        color: #0f172a !important;
        font-weight: 700;
        font-size: 7pt;
    }

    .print-items-table td:nth-child(2),
    .print-items-table th:nth-child(2) {
        text-align: right;
    }

    .print-items-table th:first-child,
    .print-items-table td:first-child {
        width: 7%;
    }

    .print-items-table th:nth-child(2),
    .print-items-table td:nth-child(2) {
        width: 40%;
    }

    .print-items-table th:nth-child(3),
    .print-items-table td:nth-child(3) {
        width: 15%;
    }

    .print-items-table th:nth-child(4),
    .print-items-table td:nth-child(4) {
        width: 18%;
    }

    .print-items-table th:nth-child(5),
    .print-items-table td:nth-child(5) {
        width: 20%;
    }

    .print-payment-summary {
        display: flex;
        justify-content: space-between;
        gap: 2.2mm;
        margin-top: 1mm;
        padding-top: 1.4mm;
        border-top: 1px solid #111827;
        font-size: 8pt;
    }

    .print-total-box {
        flex: 1;
        border: 1px solid #d5dee8;
        border-radius: 3px;
        background: #f8fafc;
        padding: 1.2mm 1.6mm;
    }

    .print-total-label {
        display: block;
        font-size: 7pt;
        color: #475569;
        margin-bottom: 0.7mm;
        font-weight: 700;
    }

    .print-total-value {
        display: block;
        font-size: 8pt;
        font-weight: 800;
        color: #0f172a;
        direction: ltr;
        text-align: left;
    }

    .print-total-box:last-child .print-total-value {
        color: #b45309;
    }

    .print-signatures {
        display: flex;
        justify-content: space-between;
        gap: 3mm;
        margin-top: 1.5mm;
        padding-top: 1.4mm;
        border-top: 1px dashed #94a3b8;
    }

    .print-signature-box {
        flex: 1;
        text-align: center;
    }

    .print-signature-label {
        display: block;
        font-size: 7pt;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.8mm;
    }

    .print-signature-line {
        height: 8mm;
        border-bottom: 1px solid #475569;
    }

    .print-stamp-box {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .print-stamp-image {
        max-width: 18mm;
        max-height: 18mm;
        object-fit: contain;
        opacity: 0.85;
    }

    @page {
        size: A4 portrait;
        margin: 5mm;
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
