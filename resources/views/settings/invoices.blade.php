@extends('layouts.app')

@section('title', 'إعدادات الفواتير')

@section('content')
<div class="page-header">
    <div>
        <h1>🧾 إعدادات الفواتير</h1>
        <p>تعديل إعدادات الفواتير والطباعة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('settings.index') }}" class="btn">← رجوع للإعدادات</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('settings.invoices.update') }}" method="POST">
            @csrf

            <h3 style="margin-bottom: 16px;">📄 إعدادات الترقيم</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">بادئة الفاتورة</label>
                    <input type="text" name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">رقم البداية</label>
                    <input type="number" name="invoice_start_number" class="form-control" value="{{ old('invoice_start_number', $settings['invoice_start_number'] ?? 1) }}" min="1">
                </div>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">📝 النصوص الافتراضية</h3>

            <div class="form-group">
                <label class="form-label">تذييل الفاتورة</label>
                <textarea name="invoice_footer" class="form-control" rows="3">{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">شروط الفاتورة</label>
                <textarea name="invoice_terms" class="form-control" rows="3">{{ old('invoice_terms', $settings['invoice_terms'] ?? '') }}</textarea>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">⚙️ خيارات العرض</h3>

            <div class="checkbox-grid">
                <label class="checkbox-item">
                    <input type="checkbox" name="show_logo_on_invoice" value="1" {{ old('show_logo_on_invoice', $settings['show_logo_on_invoice'] ?? false) ? 'checked' : '' }}>
                    <span>إظهار شعار الشركة</span>
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="show_customer_balance" value="1" {{ old('show_customer_balance', $settings['show_customer_balance'] ?? false) ? 'checked' : '' }}>
                    <span>إظهار رصيد العميل</span>
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="show_item_discount" value="1" {{ old('show_item_discount', $settings['show_item_discount'] ?? false) ? 'checked' : '' }}>
                    <span>إظهار خصم الصنف</span>
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="show_item_tax" value="1" {{ old('show_item_tax', $settings['show_item_tax'] ?? false) ? 'checked' : '' }}>
                    <span>إظهار ضريبة الصنف</span>
                </label>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-label">مدة السداد الافتراضية (يوم)</label>
                <input type="number" name="default_payment_terms_days" class="form-control" value="{{ old('default_payment_terms_days', $settings['default_payment_terms_days'] ?? 30) }}" min="0">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ الإعدادات</button>
                <a href="{{ route('settings.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.checkbox-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; }
.checkbox-item { display: flex; align-items: center; gap: 0.5rem; }
</style>
@endsection
