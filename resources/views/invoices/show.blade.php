@extends('layouts.app')

@section('title', 'عرض الفاتورة')

@section('content')
<div class="page-header">
    <div>
        <h1>🧾 {{ $invoice->invoice_number }}</h1>
        <p>تفاصيل الفاتورة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-primary" target="_blank">🖨️ طباعة</a>
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn">تعديل</a>
        <a href="{{ route('invoices.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات العميل</h3>
            <p><strong>الاسم:</strong> {{ $invoice->customer->name ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $invoice->customer->phone ?? '-' }}</p>
            <p><strong>العنوان:</strong> {{ $invoice->customer->address ?? '-' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>بيانات الفاتورة</h3>
            <p><strong>التاريخ:</strong> {{ $invoice->invoice_date?->format('Y-m-d') }}</p>
            <p><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</p>
            <p><strong>المستودع:</strong> {{ $invoice->warehouse->name ?? '-' }}</p>
            <p><strong>المندوب:</strong> {{ $invoice->salesRep->name ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="table-container  overflow-auto">
        <table class="table text-nowrap">
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
            <tfoot>
                <tr>
                    <td colspan="5" class="text-left"><strong>الإجمالي الفرعي</strong></td>
                    <td><strong>{{ number_format($invoice->subtotal, 2) }} ج.م</strong></td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td colspan="5" class="text-left">الخصم</td>
                    <td>- {{ number_format($invoice->discount_amount, 2) }} ج.م</td>
                </tr>
                @endif
                <tr>
                    <td colspan="5" class="text-left"><strong>الإجمالي النهائي</strong></td>
                    <td><strong style="color: var(--primary); font-size: 1.25rem;">{{ number_format($invoice->total_amount, 2) }} ج.م</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
</style>
@endsection
