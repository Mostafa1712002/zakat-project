<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>فاتورة {{ $sale->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Tahoma', 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
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
            font-size: 18pt;
            font-weight: bold;
            color: #0891b2;
        }

        .invoice-label {
            font-size: 14pt;
            font-weight: bold;
            color: #374151;
        }

        .invoice-number {
            font-size: 13pt;
            font-weight: bold;
            color: #0891b2;
            margin-top: 4px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 4px;
        }

        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-confirmed { background: #dbeafe; color: #1d4ed8; }
        .status-delivered { background: #d1fae5; color: #059669; }
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
            font-size: 10pt;
            font-weight: bold;
            color: #0891b2;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .info-row {
            font-size: 9.5pt;
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
            padding: 8px 6px;
            font-size: 9.5pt;
            text-align: center;
            font-weight: 600;
        }

        .items-table td {
            padding: 7px 6px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9.5pt;
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
            padding: 5px 10px;
            font-size: 10pt;
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
            font-size: 12pt;
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
            font-size: 9pt;
            color: #6b7280;
        }

        .stamp-img {
            max-width: 80px;
            max-height: 80px;
            opacity: 0.85;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-right">
            @if(!empty($companyLogo))
                <img src="{{ public_path('storage/' . $companyLogo) }}" alt="{{ $companyName }}" class="company-logo"><br>
            @endif
            <div class="company-name">{{ $companyName ?: 'الشركة' }}</div>
        </div>
        <div class="header-left">
            <div class="invoice-label">فاتورة مبيعات</div>
            <div class="invoice-number">{{ $sale->invoice_number }}</div>
            @switch($sale->status)
                @case('draft')<span class="status-badge status-draft">مسودة</span>@break
                @case('confirmed')<span class="status-badge status-confirmed">مؤكدة</span>@break
                @case('delivered')<span class="status-badge status-delivered">تم التسليم</span>@break
                @case('cancelled')<span class="status-badge status-cancelled">ملغاة</span>@break
            @endswitch
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-title">بيانات العميل</div>
            <div class="info-row">
                <span class="info-row-label">الاسم: </span>
                <span class="info-row-value">{{ $sale->customer->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-label">الهاتف: </span>
                <span class="info-row-value">{{ $sale->customer->phone ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-label">العنوان: </span>
                <span class="info-row-value">{{ $sale->customer->address ?? '-' }}</span>
            </div>
        </div>
        <div class="info-spacer"></div>
        <div class="info-box">
            <div class="info-title">بيانات الفاتورة</div>
            <div class="info-row">
                <span class="info-row-label">التاريخ: </span>
                <span class="info-row-value">{{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-label">الاستحقاق: </span>
                <span class="info-row-value">{{ $sale->due_date?->format('Y-m-d') ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-label">نوع الدفع: </span>
                <span class="info-row-value">{{ ($sale->payment_type ?? '') === 'credit' ? 'آجل' : 'نقدي' }}</span>
            </div>
            @if($sale->salesRep)
            <div class="info-row">
                <span class="info-row-label">المندوب: </span>
                <span class="info-row-value">{{ $sale->salesRep->name }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th style="width: 38%;">الصنف</th>
                <th style="width: 12%;">الكمية</th>
                <th style="width: 16%;">سعر الوحدة</th>
                <th style="width: 12%;">الخدمة</th>
                <th style="width: 16%;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="name">{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->discount_amount, 2) }}</td>
                <td>{{ number_format($item->total ?? $item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="total-label">الإجمالي الفرعي</td>
                <td class="total-value">{{ number_format($sale->subtotal, 2) }} ج.م</td>
            </tr>
            @if($sale->discount_amount > 0)
            <tr class="discount-row">
                <td class="total-label">الخدمة</td>
                <td class="total-value">- {{ number_format($sale->discount_amount, 2) }} ج.م</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="total-label">الإجمالي النهائي</td>
                <td class="total-value">{{ number_format($sale->total_amount, 2) }} ج.م</td>
            </tr>
            @if($sale->paid_amount > 0)
            <tr class="paid-row">
                <td class="total-label">المدفوع</td>
                <td class="total-value">{{ number_format($sale->paid_amount, 2) }} ج.م</td>
            </tr>
            <tr class="remaining-row">
                <td class="total-label">المتبقي</td>
                <td class="total-value">{{ number_format($sale->remaining_amount, 2) }} ج.م</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">توقيع العميل</div>
        </div>
        <div class="sig-box">
            @if(!empty($companyStamp))
                <img src="{{ public_path('storage/' . $companyStamp) }}" alt="ختم الشركة" class="stamp-img">
            @else
                <div class="sig-line"></div>
                <div class="sig-label">ختم الشركة</div>
            @endif
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">توقيع المندوب</div>
        </div>
    </div>
</body>
</html>
