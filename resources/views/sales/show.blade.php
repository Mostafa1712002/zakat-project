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
        <!-- Invoice Header - مقسوم نصين جنب بعض -->
        <div class="invoice-header">
            <div class="header-left">
                <div class="invoice-info">
                    <span class="invoice-label">فاتورة مبيعات</span>
                    <span class="invoice-number">{{ $sale->invoice_number }}</span>
                </div>
                <div class="invoice-status no-print">
                    @switch($sale->status)
                        @case('draft')<span class="status-badge status-draft">مسودة</span>@break
                        @case('confirmed')<span class="status-badge status-confirmed">مؤكدة</span>@break
                        @case('delivered')<span class="status-badge status-delivered">تم التسليم</span>@break
                        @case('cancelled')<span class="status-badge status-cancelled">ملغاة</span>@break
                    @endswitch
                </div>
            </div>
            <div class="header-right">
                <h1 class="company-name">{{ config('app.name', 'شركة روجينس') }}</h1>
                <p class="company-slogan">للتجارة والتوزيع</p>
            </div>
        </div>

        <!-- Contact Numbers Bar -->
        <div class="contacts-bar">
            <div class="contact-item">
                <span class="contact-icon">📞</span>
                <span class="contact-label">مشرف الخط:</span>
                <span class="contact-value">01xxxxxxxxx</span>
            </div>
            @if($sale->salesRep)
            <div class="contact-item">
                <span class="contact-icon">👤</span>
                <span class="contact-label">المندوب:</span>
                <span class="contact-value">{{ $sale->salesRep->name }} {{ $sale->salesRep->phone ? '- ' . $sale->salesRep->phone : '' }}</span>
            </div>
            @endif
        </div>

        <!-- Info Grid - صف واحد جنب بعض -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-box-header">
                    <span class="info-icon">📋</span>
                    <h4>بيانات الفاتورة</h4>
                </div>
                <div class="info-box-body">
                    <table class="info-table">
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

            <div class="info-box">
                <div class="info-box-header">
                    <span class="info-icon">👤</span>
                    <h4>بيانات العميل</h4>
                </div>
                <div class="info-box-body">
                    <table class="info-table">
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
        </div>

        <!-- Items Table -->
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 30%;">الصنف</th>
                        <th style="width: 10%;">الكمية</th>
                        <th style="width: 15%;">سعر الوحدة</th>
                        <th style="width: 10%;">الخدمة</th>
                        <th style="width: 10%;">الضريبة</th>
                        <th style="width: 20%;">الإجمالي</th>
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
                        <td>{{ number_format($item->tax_amount, 2) }} ج.م</td>
                        <td>{{ number_format($item->total ?? $item->subtotal, 2) }} ج.م</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row subtotal-row">
                    <span class="total-label">الإجمالي الفرعي:</span>
                    <span class="total-value">{{ number_format($sale->subtotal, 2) }} ج.م</span>
                </div>
                @if($sale->discount_amount > 0)
                <div class="total-row">
                    <span class="total-label">الخصم:</span>
                    <span class="total-value discount">- {{ number_format($sale->discount_amount, 2) }} ج.م</span>
                </div>
                @endif
                <div class="total-row grand-total-row">
                    <span class="total-label">الإجمالي النهائي:</span>
                    <span class="total-value grand-total">{{ number_format($sale->total_amount, 2) }} ج.م</span>
                </div>
                @if($sale->paid_amount > 0)
                <div class="total-row">
                    <span class="total-label">المدفوع:</span>
                    <span class="total-value paid">{{ number_format($sale->paid_amount, 2) }} ج.م</span>
                </div>
                <div class="total-row">
                    <span class="total-label">المتبقي:</span>
                    <span class="total-value remaining">{{ number_format($sale->remaining_amount, 2) }} ج.م</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Signatures Section - سطر واحد جنب بعض -->
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

        @if($sale->payments->count() > 0)
        <div class="card payments-card no-print">
            <div class="card-body">
                <h3>الدفعات</h3>
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
        @endif

        @if($sale->notes)
        <div class="card notes-card no-print">
            <div class="card-body">
                <h3>ملاحظات</h3>
                <p>{{ $sale->notes }}</p>
            </div>
        </div>
        @endif
    </div>
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

.btn-icon {
    font-size: 1.2rem;
}

/* Invoice Container */
.invoice-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 30px;
    border: 1px solid #e5e7eb;
    max-width: 100%;
    overflow: visible;
}

/* Invoice Header - مقسوم نصين جنب بعض */
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 3px solid #0ea5e9;
    margin-bottom: 20px;
}

.header-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.header-left {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
}

.invoice-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
}

.company-name {
    font-size: 1.8rem;
    font-weight: bold;
    color: #0ea5e9;
    line-height: 1.2;
}

.company-slogan {
    font-size: 0.9rem;
    color: #6b7280;
    margin-top: 2px;
}

.invoice-label {
    font-size: 1.3rem;
    color: #374151;
    font-weight: 600;
}

.invoice-number {
    font-size: 1.4rem;
    font-weight: bold;
    color: #0ea5e9;
}

.invoice-status {
    margin-top: 5px;
}

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

.contact-icon {
    font-size: 1.1rem;
}

.contact-label {
    font-weight: 600;
    color: #374151;
}

.contact-value {
    color: #0ea5e9;
    font-weight: bold;
    direction: ltr;
}

/* Info Grid - صف واحد جنب بعض */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
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
    padding: 10px 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e5e7eb;
}

.info-box-header h4 {
    margin: 0;
    font-size: 0.95rem;
    color: #0ea5e9;
    font-weight: 600;
}

.info-icon {
    font-size: 1.1rem;
}

.info-box-body {
    padding: 10px 16px;
}

.info-table {
    width: 100%;
}

.info-table tr td {
    padding: 5px 0;
    vertical-align: top;
}

.info-label {
    color: #6b7280;
    width: 40%;
    font-size: 0.85rem;
}

.info-value {
    color: #111827;
    font-size: 0.85rem;
}

.payment-type {
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.payment-type.cash { background: #d1fae5; color: #059669; }
.payment-type.credit { background: #fef3c7; color: #d97706; }

.payment-badge {
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    display: inline-block;
}

.payment-badge.unpaid { background: #fee2e2; color: #dc2626; }
.payment-badge.partial { background: #fef3c7; color: #d97706; }
.payment-badge.paid { background: #d1fae5; color: #059669; }
.payment-badge.overdue { background: #ffedd5; color: #ea580c; }

/* Card & Table */
.card {
    margin-bottom: 20px;
    overflow-x: visible;
    overflow-y: visible;
}

.table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    overflow: visible;
}

.table th {
    background: #0ea5e9;
    color: white;
    padding: 8px 6px;
    font-size: 0.8rem;
    text-align: center;
    font-weight: 600;
    word-wrap: break-word;
    overflow: hidden;
}

.table td {
    padding: 8px 6px;
    border-bottom: 1px solid #e5e7eb;
    text-align: center;
    font-size: 0.8rem;
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table tbody tr:hover {
    background: #f9fafb;
}

/* Totals Section */
.totals-section {
    margin-bottom: 30px;
    display: flex;
    justify-content: flex-end;
}

.totals-box {
    width: 100%;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.total-row:last-child {
    border-bottom: none;
}

.total-label {
    color: #6b7280;
    font-size: 0.85rem;
}

.total-value {
    font-weight: 600;
    font-size: 0.95rem;
    direction: ltr;
}

.total-value.discount {
    color: #dc2626;
}

.total-value.paid {
    color: #059669;
}

.total-value.remaining {
    color: #d97706;
}

.grand-total-row {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 6px;
    padding: 10px 15px !important;
    margin: 5px 0;
    border: 2px solid #0ea5e9 !important;
}

.grand-total-row .total-label {
    font-weight: bold;
    font-size: 0.95rem;
    color: #111827;
}

.grand-total-row .total-value {
    font-size: 1.2rem;
    color: #0ea5e9;
}

.subtotal-row {
    background: white;
}

/* Signatures Section - سطر واحد جنب بعض */
.signatures-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin: 30px 0 20px;
    padding-top: 20px;
    border-top: 1px dashed #d1d5db;
    gap: 80px;
}

.signature-box {
    text-align: center;
    flex: 1;
}

.signature-line {
    border-bottom: 2px solid #374151;
    height: 50px;
    margin-bottom: 10px;
}

.signature-box span {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 500;
}

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
    }

    .no-print,
    .sidebar,
    nav,
    header,
    footer,
    .invoice-actions,
    .payments-card,
    .notes-card {
        display: none !important;
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

    .invoice-header {
        padding-bottom: 6px;
        margin-bottom: 10px;
        border-bottom: 2px solid #0ea5e9;
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
    }

    .header-right {
        text-align: right;
        flex: 1;
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-end !important;
    }

    .header-left {
        flex: 1;
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
    }

    .invoice-info {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
    }

    .company-name {
        font-size: 13pt;
        line-height: 1.3;
        display: block;
    }

    .company-slogan {
        font-size: 8pt;
        margin-top: 2px;
        display: block;
    }

    .invoice-label {
        font-size: 11pt;
        line-height: 1.3;
        display: block;
    }

    .invoice-number {
        font-size: 12pt;
        margin-top: 3px;
        display: block;
    }

    .contacts-bar {
        background: #f0f9ff !important;
        padding: 5px 10px;
        margin-bottom: 10px;
        gap: 30px;
        border-radius: 4px;
        display: flex !important;
        flex-direction: row !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
    }

    .contact-item {
        gap: 5px;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
    }

    .contact-icon {
        font-size: 9pt;
    }

    .contact-label {
        font-size: 7.5pt;
    }

    .contact-value {
        font-size: 7.5pt;
        font-weight: 600;
    }

    .info-grid {
        gap: 10px;
        margin-bottom: 10px;
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
    }

    .info-box {
        border: 1px solid #d1d5db;
        border-radius: 4px;
        overflow: hidden;
    }

    .info-box-header {
        background: #f3f4f6 !important;
        padding: 5px 8px;
        border-bottom: 1px solid #d1d5db;
    }

    .info-box-header h4 {
        font-size: 8.5pt;
        margin: 0;
        font-weight: 600;
    }

    .info-icon {
        font-size: 9pt;
    }

    .info-box-body {
        padding: 6px 8px;
        background: white;
    }

    .info-table {
        width: 100%;
    }

    .info-table tr td {
        padding: 2px 0;
        font-size: 7.5pt;
        line-height: 1.5;
    }

    .info-label {
        font-size: 7.5pt;
        width: 35%;
        color: #6b7280;
    }

    .info-value {
        font-size: 7.5pt;
        color: #111827;
        font-weight: 500;
    }

    .payment-type {
        font-size: 6.5pt;
        padding: 2px 6px;
    }

    .payment-badge {
        font-size: 6.5pt;
        padding: 2px 6px;
    }

    .card {
        margin-bottom: 10px;
    }

    .table {
        margin-bottom: 8px;
        border-collapse: collapse;
        width: 100%;
    }

    .table th {
        background: #0ea5e9 !important;
        color: white !important;
        padding: 4px 3px;
        font-size: 7.5pt;
        line-height: 1.3;
        font-weight: 600;
        border: none;
    }

    .table td {
        padding: 3px 2px;
        font-size: 7.5pt;
        line-height: 1.4;
        border-bottom: 0.5px solid #e5e7eb;
    }

    .table tbody tr:nth-child(even) {
        background: #f9fafb !important;
    }

    .table tbody tr:hover {
        background: #f3f4f6 !important;
    }

    .totals-section {
        margin-bottom: 10px;
    }

    .totals-box {
        background: #fafafa !important;
        border: 1px solid #d1d5db !important;
        padding: 6px 10px;
        border-radius: 4px;
    }

    .total-row {
        padding: 3px 0;
        font-size: 7.5pt;
        line-height: 1.5;
        display: flex;
        justify-content: space-between;
    }

    .total-label {
        font-size: 7.5pt;
        color: #6b7280;
    }

    .total-value {
        font-size: 7.5pt;
        font-weight: 600;
        color: #111827;
    }

    .subtotal-row {
        border-bottom: 0.5px solid #d1d5db;
        padding-bottom: 4px;
        margin-bottom: 4px;
    }

    .grand-total-row {
        background: #e0f2fe !important;
        border: 1.5px solid #0ea5e9 !important;
        padding: 5px 8px !important;
        margin: 4px 0;
        border-radius: 3px;
    }

    .grand-total-row .total-label {
        font-size: 9pt;
        font-weight: bold;
        color: #0c4a6e;
    }

    .grand-total-row .total-value {
        font-size: 10pt;
        font-weight: bold;
        color: #0ea5e9;
    }

    .signatures-section {
        margin: 15px 0 8px;
        padding-top: 10px;
        gap: 60px;
        border-top: 1px dashed #9ca3af;
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
    }

    .signature-box {
        flex: 1;
        text-align: center;
    }

    .signature-line {
        height: 30px;
        border-bottom: 1px solid #6b7280;
        margin-bottom: 5px;
    }

    .signature-box span {
        font-size: 7.5pt;
        color: #6b7280;
        font-weight: 500;
    }

    @page {
        size: A4 portrait;
        margin: 12mm 10mm;
    }

    body {
        margin: 0;
        padding: 0;
        overflow: visible;
    }

    * {
        box-sizing: border-box;
        overflow: visible;
    }

    .invoice-wrapper {
        max-width: 100%;
        overflow: visible;
    }

    .invoice-container {
        overflow: visible;
        max-width: 100%;
    }

    .card,
    .table {
        overflow: visible;
        max-width: 100%;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .invoice-header {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .header-right,
    .header-left {
        align-items: center;
        text-align: center;
    }

    .invoice-info {
        align-items: center;
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

    .signature-box {
        width: 100%;
        max-width: 250px;
    }
}
</style>
@endpush