@extends('layouts.app')

@section('title', 'تسجيل دفعة')

@section('content')
<div class="page-header">
    <h1>💰 تسجيل دفعة جديدة</h1>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.payments.store') }}" class="card form-card">
    @csrf

    <div class="form-row">
        <label>الفاتورة <span class="required">*</span></label>
        <select name="invoice_id" required>
            <option value="">— اختر فاتورة —</option>
            @foreach ($invoices as $inv)
                <option value="{{ $inv->id }}"
                    @selected($selectedInvoice?->id === $inv->id)
                    data-remaining="{{ $inv->grand_total - $inv->paid_amount }}">
                    {{ $inv->invoice_number }} — {{ $inv->customer?->name }}
                    (متبقي: {{ number_format($inv->grand_total - $inv->paid_amount, 2) }} ر.س)
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-row">
        <label>المبلغ <span class="required">*</span></label>
        <input type="number" name="amount" step="0.01" min="0.01" required value="{{ old('amount') }}">
    </div>

    <div class="form-row">
        <label>طريقة الدفع <span class="required">*</span></label>
        <select name="method" required>
            <option value="cash">نقدي</option>
            <option value="bank">بنكي</option>
            <option value="transfer">تحويل</option>
            <option value="check">شيك</option>
        </select>
    </div>

    <div class="form-row">
        <label>الخزينة <span class="required">*</span></label>
        <select name="treasury_id" required>
            @foreach ($treasuries as $t)
                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->type === 'cash' ? 'نقدي' : 'بنكي' }})</option>
            @endforeach
        </select>
    </div>

    <div class="form-row">
        <label>المرجع</label>
        <input type="text" name="reference_number" value="{{ old('reference_number') }}">
    </div>

    <div class="form-row">
        <label>تاريخ الدفعة <span class="required">*</span></label>
        <input type="date" name="payment_date" required value="{{ old('payment_date', now()->toDateString()) }}">
    </div>

    <div class="form-row">
        <label>ملاحظات</label>
        <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 تسجيل</button>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">إلغاء</a>
    </div>
</form>
@endsection
