@extends('layouts.app')

@section('title', 'عرض فاتورة البيع')

@section('content')
<div class="invoice-wrapper">
    <!-- Action Buttons - Screen Only -->
    <div class="invoice-actions no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <span class="btn-icon">🖨️</span>
            طباعة الفاتورة
        </button>
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
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1 class="company-name">{{ config('app.name', 'شركة روجينس') }}</h1>
                <p class="company-slogan">للتجارة والتوزيع</p>
            </div>
            <div class="invoice-title">
                <h2>فاتورة مبيعات</h2>
                <div class="invoice-number">{{ $sale->invoice_number }}</div>
                <div class="invoice-status no-print">
                    @switch($sale->status)
                        @case('draft')<span class="status-badge status-draft">مسودة</span>@break
                        @case('confirmed')<span class="status-badge status-confirmed">مؤكدة</span>@break
                        @case('delivered')<span class="status-badge status-delivered">تم التسليم</span>@break
                        @case('cancelled')<span class="status-badge status-cancelled">ملغاة</span>@break
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
                            <td class="info-value">
                                <span class="payment-type {{ $sale->payment_type === 'credit' ? 'credit' : 'cash' }}">
                                    {{ $sale->payment_type === 'credit' ? 'آجل' : 'نقدي' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">حالة الدفع:</td>
                            <td class="info-value">
                                @switch($sale->payment_status)
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
    max-width: 900px;
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

/* Invoice Header */
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 20px;
    border-bottom: 3px double var(--primary);
    margin-bottom: 20px;
}

.company-info { text-align: right; }

.company-name {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--primary);
    margin: 0;
}

.company-slogan {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 4px 0 0 0;
}

.invoice-title { text-align: left; }

.invoice-title h2 {
    font-size: 1.3rem;
    color: #374151;
    margin: 0;
}

.invoice-number {
    font-size: 1.4rem;
    font-weight: bold;
    color: var(--primary);
    margin-top: 8px;
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
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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
    justify-content: space-around;
    margin: 40px 0 20px;
    padding-top: 20px;
    border-top: 1px dashed #d1d5db;
}

.signature-box {
    text-align: center;
    width: 180px;
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

/* ========== PRINT STYLES ========== */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        background: white !important;
        margin: 0;
        padding: 0;
        font-size: 10pt;
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
    }

    .invoice-wrapper {
        max-width: 100%;
        padding: 0;
        margin: 0;
    }

    .invoice-container {
        box-shadow: none;
        border: none;
        padding: 4mm 8mm;
        border-radius: 0;
    }

    /* Header - compact */
    .invoice-header {
        padding-bottom: 4px;
        margin-bottom: 4px;
        border-bottom: 2px solid #333;
    }

    .company-name { font-size: 14pt; }
    .company-slogan { font-size: 8pt; margin-top: 2px; }
    .invoice-title h2 { font-size: 10pt; }
    .invoice-number { font-size: 11pt; margin-top: 2px; }

    /* Contacts bar - compact */
    .contacts-bar {
        background: #f0f0f0 !important;
        padding: 4px 8px;
        margin-bottom: 4px;
        font-size: 9pt;
        gap: 20px;
        border-radius: 0;
    }

    /* Print info row - visible in print */
    .print-info-row {
        display: flex !important;
        justify-content: space-between;
        padding: 4px 0;
        margin-bottom: 4px;
        border-bottom: 1px solid #ccc;
        font-size: 10pt;
    }

    /* Items table - tight */
    .items-card {
        margin: 0;
    }

    .items-table th {
        background: #333 !important;
        color: white !important;
        padding: 4px 3px;
        font-size: 9pt;
        border-radius: 0 !important;
    }

    .items-table td {
        padding: 3px;
        font-size: 9pt;
        border-bottom: 1px solid #ddd;
    }

    .items-table tfoot td {
        padding: 3px;
        font-size: 9pt;
    }

    .items-table tbody tr:nth-child(even) {
        background: #f5f5f5 !important;
    }

    .grand-total-row {
        background: #e8e8e8 !important;
    }

    .grand-total-row td {
        padding: 5px 3px !important;
    }

    .grand-total {
        font-size: 10pt !important;
    }

    /* Signatures - compact */
    .signatures-section {
        margin: 15px 0 5px;
        padding-top: 10px;
    }

    .signature-line {
        height: 30px;
    }

    .signature-box span {
        font-size: 8pt;
    }

    @page {
        size: A4;
        margin: 3mm;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .invoice-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .company-info,
    .invoice-title {
        text-align: center;
    }

    .contacts-bar {
        flex-direction: column;
        gap: 10px;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .signatures-section {
        flex-direction: column;
        align-items: center;
        gap: 30px;
    }
}
</style>
@endpush
