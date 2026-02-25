<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>فاتورة {{ $purchase->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Naskh Arabic', 'Noto Sans Arabic', 'Tahoma', 'Arial', sans-serif;
            font-size: 13pt;
            line-height: 1.7;
            color: #1f2937;
            direction: rtl;
            padding: 15mm;
            background: #fff;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            border-bottom: 3px double #0891b2;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 50%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            text-align: left;
            width: 50%;
        }

        .company-logo {
            max-width: 80px;
            max-height: 60px;
            margin-bottom: 6px;
        }

        .company-name {
            font-size: 22pt;
            font-weight: bold;
            color: #0891b2;
        }

        .invoice-label {
            font-size: 17pt;
            font-weight: bold;
            color: #374151;
        }

        .invoice-number {
            font-size: 15pt;
            font-weight: bold;
            color: #0891b2;
            margin-top: 4px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11pt;
            font-weight: bold;
            margin-top: 4px;
        }

        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-ordered { background: #dbeafe; color: #1d4ed8; }
        .status-received { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }

        /* Info Grid */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }

        .info-box {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px 14px;
        }

        .info-spacer {
            display: table-cell;
            width: 4%;
        }

        .info-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0891b2;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .info-row {
            font-size: 12pt;
            padding: 3px 0;
        }

        .info-row-label {
            color: #6b7280;
            display: inline;
        }

        .info-row-value {
            font-weight: 600;
            display: inline;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .items-table th {
            background: #0891b2;
            color: white;
            padding: 10px 8px;
            font-size: 12pt;
            text-align: center;
            font-weight: 600;
        }

        .items-table td {
            padding: 9px 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12pt;
        }

        .items-table td.name {
            text-align: right;
            font-weight: 500;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        /* Totals */
        .totals-section {
            width: 100%;
            margin-bottom: 16px;
        }

        .totals-table {
            width: 45%;
            margin-right: auto;
            margin-left: 0;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 12px;
            font-size: 13pt;
        }

        .totals-table .total-label {
            text-align: right;
            color: #6b7280;
        }

        .totals-table .total-value {
            text-align: left;
            font-weight: 600;
            direction: ltr;
        }

        .totals-table .grand-total td {
            border-top: 2px solid #0891b2;
            font-size: 15pt;
            font-weight: bold;
            color: #0891b2;
            padding-top: 8px;
        }

        .totals-table .discount-row .total-value {
            color: #dc2626;
        }

        .totals-table .paid-row .total-value {
            color: #059669;
        }

        .totals-table .remaining-row .total-value {
            color: #d97706;
        }

        /* Signatures */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px dashed #d1d5db;
        }

        .sig-box {
            display: table-cell;
            text-align: center;
            vertical-align: bottom;
            width: 33.33%;
        }

        .sig-line {
            border-bottom: 1px solid #374151;
            height: 50px;
            margin: 0 20px 6px;
        }

        .sig-label {
            font-size: 12pt;
            color: #6b7280;
        }

        .stamp-img {
            max-width: 80px;
            max-height: 80px;
            opacity: 0.85;
        }

        /* Header Contacts */
        .header-contacts {
            margin-top: 4px;
        }

        .header-contact-item {
            font-size: 11pt;
            color: #374151;
            margin-left: 14px;
            display: inline;
        }

        .header-contact-item .contact-phone {
            color: #0891b2;
            font-weight: 600;
            direction: ltr;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-right">
            @if(!empty($companyLogo) && file_exists(public_path('storage/' . $companyLogo)))
                <img src="file://{{ public_path('storage/' . $companyLogo) }}" alt="{{ $companyName }}" class="company-logo"><br>
            @endif
            <div class="company-name">{{ $companyName ?: 'الشركة' }}</div>
            @if(!empty($invoiceContacts))
            <div class="header-contacts">
                @foreach($invoiceContacts as $contact)
                <span class="header-contact-item">{{ $contact['name'] }}: <span class="contact-phone">{{ $contact['phone'] }}</span></span>
                @endforeach
            </div>
            @endif
        </div>
        <div class="header-left">
            <div class="invoice-label">فاتورة مشتريات</div>
            <div class="invoice-number">{{ $purchase->invoice_number }}</div>
            @switch($purchase->status)
                @case('draft')<span class="status-badge status-draft">مسودة</span>@break
                @case('ordered')<span class="status-badge status-ordered">تم الطلب</span>@break
                @case('received')<span class="status-badge status-received">مستلم</span>@break
                @case('cancelled')<span class="status-badge status-cancelled">ملغي</span>@break
            @endswitch
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-title">بيانات المورد</div>
            <div class="info-row">
                <span class="info-row-label">الاسم: </span>
                <span class="info-row-value">{{ $purchase->supplier->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-label">الهاتف: </span>
                <span class="info-row-value">{{ $purchase->supplier->phone ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-label">العنوان: </span>
                <span class="info-row-value">{{ $purchase->supplier->address ?? '-' }}</span>
            </div>
        </div>
        <div class="info-spacer"></div>
        <div class="info-box">
            <div class="info-title">بيانات الفاتورة</div>
            <div class="info-row">
                <span class="info-row-label">التاريخ: </span>
                <span class="info-row-value">{{ $purchase->invoice_date?->format('Y-m-d') ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-label">الاستحقاق: </span>
                <span class="info-row-value">{{ $purchase->due_date?->format('Y-m-d') ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-label">المخزن: </span>
                <span class="info-row-value">{{ $purchase->warehouse->name ?? '-' }}</span>
            </div>
            @if($purchase->supplier_invoice_number)
            <div class="info-row">
                <span class="info-row-label">رقم فاتورة المورد: </span>
                <span class="info-row-value">{{ $purchase->supplier_invoice_number }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 7%;">#</th>
                <th style="width: 43%;">الصنف</th>
                <th style="width: 15%;">الكمية</th>
                <th style="width: 17%;">سعر الوحدة</th>
                <th style="width: 18%;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="name">{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ number_format($item->unit_cost ?? $item->unit_price, 2) }}</td>
                <td>{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="total-label">الإجمالي الفرعي</td>
                <td class="total-value">{{ number_format($purchase->subtotal, 2) }} ج.م</td>
            </tr>
            @if($purchase->discount_amount > 0)
            <tr class="discount-row">
                <td class="total-label">الخصم</td>
                <td class="total-value">- {{ number_format($purchase->discount_amount, 2) }} ج.م</td>
            </tr>
            @endif
            @if($purchase->shipping_amount > 0)
            <tr>
                <td class="total-label">الشحن</td>
                <td class="total-value">{{ number_format($purchase->shipping_amount, 2) }} ج.م</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="total-label">الإجمالي النهائي</td>
                <td class="total-value">{{ number_format($purchase->total_amount, 2) }} ج.م</td>
            </tr>
            @if($purchase->paid_amount > 0)
            <tr class="paid-row">
                <td class="total-label">المدفوع</td>
                <td class="total-value">{{ number_format($purchase->paid_amount, 2) }} ج.م</td>
            </tr>
            <tr class="remaining-row">
                <td class="total-label">المتبقي</td>
                <td class="total-value">{{ number_format($purchase->remaining_amount, 2) }} ج.م</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">توقيع المورد</div>
        </div>
        <div class="sig-box">
            @if(!empty($companyStamp) && file_exists(public_path('storage/' . $companyStamp)))
                <img src="file://{{ public_path('storage/' . $companyStamp) }}" alt="ختم الشركة" class="stamp-img">
            @else
                <div class="sig-line"></div>
                <div class="sig-label">ختم الشركة</div>
            @endif
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">توقيع المستلم</div>
        </div>
    </div>

</body>
</html>
