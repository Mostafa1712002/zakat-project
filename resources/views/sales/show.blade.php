@extends('layouts.app')

@section('title', 'عرض فاتورة البيع')

@section('content')
<div class="page-header">
    <div>
        <h1>💰 {{ $sale->invoice_number }}</h1>
        <p>تفاصيل فاتورة البيع</p>
    </div>
    <div class="header-actions">
        @if($sale->status === 'draft')
            <a href="{{ route('sales.edit', $sale) }}" class="btn">تعديل</a>
        @endif
        <a href="{{ route('sales.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات العميل</h3>
            <p><strong>الاسم:</strong> {{ $sale->customer->name ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $sale->customer->phone ?? '-' }}</p>
            <p><strong>البريد:</strong> {{ $sale->customer->email ?? '-' }}</p>
            <p><strong>العنوان:</strong> {{ $sale->customer->address ?? '-' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>بيانات الفاتورة</h3>
            <p><strong>التاريخ:</strong> {{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</p>
            <p><strong>تاريخ الاستحقاق:</strong> {{ $sale->due_date?->format('Y-m-d') ?? '-' }}</p>
            <p><strong>الفرع:</strong> {{ $sale->branch->name ?? '-' }}</p>
            <p><strong>المستودع:</strong> {{ $sale->warehouse->name ?? '-' }}</p>
            <p><strong>المندوب:</strong> {{ $sale->salesRep->name ?? '-' }}</p>
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
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>الخصم</th>
                    <th>الضريبة</th>
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
                    <td>{{ number_format($item->tax_amount, 2) }} ج.م</td>
                    <td>{{ number_format($item->total ?? $item->subtotal, 2) }} ج.م</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-left"><strong>الإجمالي الفرعي</strong></td>
                    <td><strong>{{ number_format($sale->subtotal, 2) }} ج.م</strong></td>
                </tr>
                @if($sale->discount_amount > 0)
                <tr>
                    <td colspan="6" class="text-left">خصم الفاتورة</td>
                    <td>- {{ number_format($sale->discount_amount, 2) }} ج.م</td>
                </tr>
                @endif
                @if($sale->tax_amount > 0)
                <tr>
                    <td colspan="6" class="text-left">الضريبة</td>
                    <td>{{ number_format($sale->tax_amount, 2) }} ج.م</td>
                </tr>
                @endif
                @if($sale->shipping_amount > 0)
                <tr>
                    <td colspan="6" class="text-left">الشحن</td>
                    <td>{{ number_format($sale->shipping_amount, 2) }} ج.م</td>
                </tr>
                @endif
                <tr>
                    <td colspan="6" class="text-left"><strong>الإجمالي النهائي</strong></td>
                    <td><strong style="color: var(--primary); font-size: 1.25rem;">{{ number_format($sale->total_amount, 2) }} ج.م</strong></td>
                </tr>
                @if($sale->paid_amount > 0)
                <tr>
                    <td colspan="6" class="text-left">المدفوع</td>
                    <td>{{ number_format($sale->paid_amount, 2) }} ج.م</td>
                </tr>
                <tr>
                    <td colspan="6" class="text-left">المتبقي</td>
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
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $sale->notes }}</p>
    </div>
</div>
@endif

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
</style>
@endsection
