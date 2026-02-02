@extends('layouts.app')

@section('title', 'إضافة مورد')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة مورد جديد</h1>
        <p>إضافة مورد جديد للنظام</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('suppliers.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم المورد *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">كود المورد</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="سيتم توليده تلقائياً" style="background: rgba(0,0,0,0.05);">
                    <small style="color: #64748b; font-size: 12px;">⚡ يتم توليده تلقائياً إذا تُرك فارغاً</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label for="mobile" class="form-label">الموبايل</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contact_person" class="form-label">جهة الاتصال</label>
                    <input type="text" name="contact_person" id="contact_person" class="form-control" value="{{ old('contact_person') }}">
                </div>

                <div class="form-group">
                    <label for="payment_terms_days" class="form-label">شروط الدفع (أيام)</label>
                    <input type="number" name="payment_terms_days" id="payment_terms_days" class="form-control" value="{{ old('payment_terms_days', 30) }}" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="address" class="form-label">العنوان</label>
                    <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}">
                </div>

                <div class="form-group">
                    <label for="city" class="form-label">المدينة</label>
                    <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="is_active" class="form-label">الحالة</label>
                <select name="is_active" id="is_active" class="form-control">
                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 حفظ</button>
                <a href="{{ route('suppliers.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
