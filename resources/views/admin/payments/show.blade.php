@extends('layouts.app')

@section('title', 'دفعة ' . $payment->payment_number)

@section('content')
@php
    $methodLabels = ['cash' => 'نقدي', 'bank' => 'بنكي', 'transfer' => 'تحويل', 'check' => 'شيك'];
@endphp
<div class="page-header">
    <h1>🧾 إيصال {{ $payment->payment_number }}</h1>
    <div class="header-actions">
        @if (!$payment->trashed())
            <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}"
                  onsubmit="return confirm('هل تريد فعلاً استرجاع هذه الدفعة؟')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">↩️ استرجاع</button>
            </form>
        @endif
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">رجوع</a>
    </div>
</div>

<div class="card">
    <table class="info-table">
        <tr><th>رقم الدفعة</th><td>{{ $payment->payment_number }}</td></tr>
        <tr><th>الفاتورة</th><td><a href="{{ route('admin.invoices.show', $payment->invoice) }}">{{ $payment->invoice?->invoice_number }}</a></td></tr>
        <tr><th>العميل</th><td>{{ $payment->customer?->name }}</td></tr>
        <tr><th>المبلغ</th><td><strong>{{ number_format($payment->amount, 2) }} ر.س</strong></td></tr>
        <tr><th>الطريقة</th><td>{{ $methodLabels[$payment->method] ?? $payment->method }}</td></tr>
        <tr><th>المرجع</th><td>{{ $payment->reference_number ?: '—' }}</td></tr>
        <tr><th>الخزينة</th><td>{{ $payment->treasury?->name }}</td></tr>
        <tr><th>تاريخ الدفعة</th><td>{{ $payment->payment_date?->format('Y-m-d') }}</td></tr>
        <tr><th>تم بواسطة</th><td>{{ $payment->creator?->name }}</td></tr>
        <tr><th>ملاحظات</th><td>{{ $payment->notes ?: '—' }}</td></tr>
    </table>
</div>
@endsection
