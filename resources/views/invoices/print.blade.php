<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
            background: #fff;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #0891b2;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-info h1 {
            font-size: 24px;
            color: #0891b2;
            margin-bottom: 5px;
        }
        .company-info p {
            color: #666;
            font-size: 12px;
        }
        .invoice-title {
            text-align: left;
        }
        .invoice-title h2 {
            font-size: 28px;
            color: #0891b2;
        }
        .invoice-title .invoice-number {
            font-size: 16px;
            color: #666;
        }
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .meta-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }
        .meta-box h3 {
            font-size: 12px;
            color: #0891b2;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .meta-box p {
            margin-bottom: 5px;
        }
        .meta-box strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #0891b2;
            color: white;
            padding: 12px;
            text-align: right;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        .totals {
            margin-right: auto;
            width: 300px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-row.total {
            font-size: 18px;
            font-weight: 700;
            color: #0891b2;
            border-bottom: 3px solid #0891b2;
            padding-top: 15px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 10px;
        }
        .notes {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .notes h4 {
            color: #92400e;
            margin-bottom: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-unpaid { background: #fee2e2; color: #991b1b; }
        .status-partial { background: #fef3c7; color: #92400e; }
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
        .print-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #0891b2;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-btn:hover { background: #0e7490; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ طباعة</button>

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1>Rogence System</h1>
                <p>نظام إدارة شامل</p>
                <p>العنوان: القاهرة، مصر</p>
                <p>الهاتف: 01234567890</p>
            </div>
            <div class="invoice-title">
                <h2>فاتورة مبيعات</h2>
                <p class="invoice-number">{{ $invoice->invoice_number }}</p>
                <p>
                    @if($invoice->payment_status === 'paid')
                        <span class="status-badge status-paid">مدفوعة</span>
                    @elseif($invoice->payment_status === 'partial')
                        <span class="status-badge status-partial">مدفوعة جزئياً</span>
                    @else
                        <span class="status-badge status-unpaid">غير مدفوعة</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="invoice-meta">
            <div class="meta-box">
                <h3>بيانات العميل</h3>
                <p><strong>{{ $invoice->customer->name ?? 'غير محدد' }}</strong></p>
                <p>{{ $invoice->customer->phone ?? '' }}</p>
                <p>{{ $invoice->customer->address ?? '' }}</p>
            </div>
            <div class="meta-box">
                <h3>بيانات الفاتورة</h3>
                <p><strong>التاريخ:</strong> {{ $invoice->invoice_date?->format('Y-m-d') }}</p>
                <p><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</p>
                <p><strong>المستودع:</strong> {{ $invoice->warehouse->name ?? '-' }}</p>
                <p><strong>المندوب:</strong> {{ $invoice->salesRep->name ?? '-' }}</p>
            </div>
        </div>

        <table>
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
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                    <td>{{ number_format($item->discount_amount, 2) }} ج.م</td>
                    <td>{{ number_format($item->subtotal, 2) }} ج.م</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>الإجمالي الفرعي:</span>
                <span>{{ number_format($invoice->subtotal, 2) }} ج.م</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="totals-row">
                <span>الخصم:</span>
                <span>- {{ number_format($invoice->discount_amount, 2) }} ج.م</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="totals-row">
                <span>الضريبة:</span>
                <span>{{ number_format($invoice->tax_amount, 2) }} ج.م</span>
            </div>
            @endif
            @if($invoice->shipping_amount > 0)
            <div class="totals-row">
                <span>الشحن:</span>
                <span>{{ number_format($invoice->shipping_amount, 2) }} ج.م</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>الإجمالي النهائي:</span>
                <span>{{ number_format($invoice->total_amount, 2) }} ج.م</span>
            </div>
            @if($invoice->paid_amount > 0)
            <div class="totals-row">
                <span>المدفوع:</span>
                <span>{{ number_format($invoice->paid_amount, 2) }} ج.م</span>
            </div>
            <div class="totals-row">
                <span>المتبقي:</span>
                <span>{{ number_format($invoice->remaining_amount, 2) }} ج.م</span>
            </div>
            @endif
        </div>

        @if($invoice->notes)
        <div class="notes">
            <h4>ملاحظات:</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <div class="footer">
            <div class="signature-box">
                <div class="signature-line">توقيع المستلم</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">توقيع المسؤول</div>
            </div>
        </div>
    </div>
</body>
</html>
