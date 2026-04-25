@extends('layouts.app')

@section('title', 'الإعدادات العامة')

@section('content')
<div class="page-header">
    <div>
        <h1>⚙️ الإعدادات العامة</h1>
        <p>إعدادات الفاتورة، عرض السعر، الضريبة والمدفوعات</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-right:1rem">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.settings.general.update') }}" class="card" style="padding:1.5rem;max-width:720px">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="default_tax_rate">نسبة الضريبة الافتراضية (%)</label>
        <input type="number" step="0.01" min="0" max="100"
               id="default_tax_rate" name="default_tax_rate"
               value="{{ old('default_tax_rate', $settings['default_tax_rate']) }}"
               class="form-control" required>
        <small>قيمة 15 للضريبة القياسية في المملكة العربية السعودية</small>
    </div>

    <div class="form-group">
        <label for="invoice_number_prefix">بادئة رقم الفاتورة</label>
        <input type="text" maxlength="20"
               id="invoice_number_prefix" name="invoice_number_prefix"
               value="{{ old('invoice_number_prefix', $settings['invoice_number_prefix']) }}"
               class="form-control" required>
    </div>

    <div class="form-group">
        <label for="quote_number_prefix">بادئة رقم عرض السعر</label>
        <input type="text" maxlength="20"
               id="quote_number_prefix" name="quote_number_prefix"
               value="{{ old('quote_number_prefix', $settings['quote_number_prefix']) }}"
               class="form-control" required>
    </div>

    <div class="form-group">
        <label for="payment_number_prefix">بادئة رقم الدفعة</label>
        <input type="text" maxlength="20"
               id="payment_number_prefix" name="payment_number_prefix"
               value="{{ old('payment_number_prefix', $settings['payment_number_prefix']) }}"
               class="form-control" required>
    </div>

    <div class="form-group">
        <label for="invoice_due_days">أيام الاستحقاق الافتراضية للفاتورة</label>
        <input type="number" min="0" max="365"
               id="invoice_due_days" name="invoice_due_days"
               value="{{ old('invoice_due_days', $settings['invoice_due_days']) }}"
               class="form-control" required>
    </div>

    <div class="form-group">
        <label for="quote_validity_days">أيام صلاحية عرض السعر</label>
        <input type="number" min="0" max="365"
               id="quote_validity_days" name="quote_validity_days"
               value="{{ old('quote_validity_days', $settings['quote_validity_days']) }}"
               class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
</form>
@endsection
