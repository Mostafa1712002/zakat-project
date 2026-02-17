@extends('layouts.app')

@section('title', 'إعدادات الشركة')

@section('content')
<div class="page-header">
    <div>
        <h1>🏢 إعدادات الشركة</h1>
        <p>تعديل بيانات الشركة ومعلومات الاتصال</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('settings.index') }}" class="btn">← رجوع للإعدادات</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('settings.company.update') }}" method="POST">
            @csrf

            <h3 style="margin-bottom: 16px;">📋 البيانات الأساسية</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="company_name" class="form-label">اسم الشركة *</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name', config('app.name')) }}" required>
                    @error('company_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tax_number" class="form-label">الرقم الضريبي</label>
                    <input type="text" name="tax_number" id="tax_number" class="form-control" value="{{ old('tax_number') }}">
                    @error('tax_number')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="commercial_register" class="form-label">السجل التجاري</label>
                    <input type="text" name="commercial_register" id="commercial_register" class="form-control" value="{{ old('commercial_register') }}">
                    @error('commercial_register')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">📞 معلومات الاتصال</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="supervisor_phone" class="form-label">هاتف مشرف الخط</label>
                <input type="text" name="supervisor_phone" id="supervisor_phone" class="form-control" value="{{ old('supervisor_phone', $settings['supervisor_phone'] ?? '') }}" placeholder="رقم هاتف مشرف الخط (يظهر في الفاتورة)">
                <small style="color: #64748b; font-size: 12px;">يظهر في فاتورة المبيعات المطبوعة</small>
                @error('supervisor_phone')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="website" class="form-label">الموقع الإلكتروني</label>
                    <input type="url" name="website" id="website" class="form-control" value="{{ old('website') }}" placeholder="https://example.com">
                    @error('website')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">العنوان</label>
                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                @error('address')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">💰 إعدادات مالية</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="currency" class="form-label">العملة</label>
                    <select name="currency" id="currency" class="form-control">
                        <option value="EGP" selected>جنيه مصري (ج.م)</option>
                        <option value="USD">دولار أمريكي ($)</option>
                        <option value="EUR">يورو (€)</option>
                    </select>
                    @error('currency')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tax_rate" class="form-label">نسبة الضريبة الافتراضية (%)</label>
                    <input type="number" step="0.01" name="tax_rate" id="tax_rate" class="form-control" value="{{ old('tax_rate', 14) }}" min="0" max="100">
                    @error('tax_rate')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ الإعدادات</button>
                <a href="{{ route('settings.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
