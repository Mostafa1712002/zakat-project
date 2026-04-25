<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Tahoma, Arial, sans-serif; font-size: 12px; color: #222; margin: 24px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #666; }
        .meta { display: flex; justify-content: space-between; margin: 16px 0; }
        .meta-block { font-size: 12px; line-height: 1.7; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: right; }
        thead { background: #eee; }
        tfoot td { font-weight: bold; }
        .totals { margin-top: 12px; width: 50%; margin-right: auto; }
        .totals td { border: none; }
        .qr { text-align: center; margin-top: 24px; }
        .qr img { max-width: 140px; }
        .footer { font-size: 10px; color: #777; margin-top: 24px; text-align: center; }
    </style>
</head>
<body>
    <h1>فاتورة ضريبية</h1>
    <div class="muted">{{ $invoice->invoice_number }}</div>

    <div class="meta">
        <div class="meta-block">
            <strong>العميل:</strong> {{ $invoice->customer?->name }}<br>
            <strong>الرقم الضريبي:</strong> {{ $invoice->customer?->vat_number ?? '—' }}<br>
            <strong>العنوان:</strong>
            {{ trim(implode(' ', array_filter([
                $invoice->customer?->street_name,
                $invoice->customer?->district,
                $invoice->customer?->city,
                $invoice->customer?->postal_code,
            ]))) }}
        </div>
        <div class="meta-block">
            <strong>تاريخ الإصدار:</strong> {{ $invoice->issued_at?->format('Y-m-d') ?? '—' }}<br>
            <strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date?->format('Y-m-d') ?? '—' }}<br>
            <strong>الفعالية:</strong> {{ $invoice->event_name }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الخدمة</th>
                <th>الوصف</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الخصم</th>
                <th>الضريبة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->service?->name }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->discount_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->tax_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>الإجمالي قبل الضريبة:</td><td>{{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
        <tr><td>إجمالي الخصومات:</td><td>{{ number_format((float) $invoice->discount_total, 2) }}</td></tr>
        <tr><td>إجمالي الضريبة (15%):</td><td>{{ number_format((float) $invoice->tax_total, 2) }}</td></tr>
        <tr><td><strong>الإجمالي النهائي:</strong></td>
            <td><strong>{{ number_format((float) $invoice->grand_total, 2) }} ر.س</strong></td></tr>
    </table>

    @if ($invoice->qr_code)
        <div class="qr">
            <img src="data:image/png;base64,{{ $invoice->qr_code }}" alt="ZATCA QR">
        </div>
    @endif

    <div class="footer">
        UUID: {{ $invoice->uuid ?? '—' }}<br>
        تم إنشاء هذه الفاتورة بواسطة نظام أمراك
    </div>
</body>
</html>
