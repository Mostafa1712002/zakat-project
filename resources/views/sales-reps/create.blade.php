@extends('layouts.app')

@section('title', 'إضافة مندوب')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة مندوب جديد</h1>
        <p>إضافة مندوب مبيعات جديد</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('sales-reps.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم المندوب *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">الكود</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="commission_rate" class="form-label">نسبة العمولة %</label>
                    <input type="number" step="0.01" name="commission_rate" id="commission_rate" class="form-control" value="{{ old('commission_rate', 0) }}" min="0" max="100">
                </div>

                <div class="form-group">
                    <label for="commission_type" class="form-label">نوع العمولة</label>
                    <select name="commission_type" id="commission_type" class="form-control">
                        <option value="percentage" {{ old('commission_type') == 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                        <option value="fixed" {{ old('commission_type') == 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sales_target" class="form-label">الهدف البيعي (ج.م)</label>
                    <input type="number" step="0.01" name="sales_target" id="sales_target" class="form-control" value="{{ old('sales_target', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label for="is_active" class="form-label">الحالة</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 حفظ</button>
                <a href="{{ route('sales-reps.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
