@extends('layouts.app')

@section('title', 'إضافة شريك')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة شريك جديد</h1>
        <p>تسجيل بيانات شريك جديد</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('partners.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('partners.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم الشريك *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="national_id" class="form-label">الرقم القومي</label>
                    <input type="text" name="national_id" id="national_id" class="form-control" value="{{ old('national_id') }}">
                    @error('national_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="ownership_percentage" class="form-label">نسبة الملكية (%) *</label>
                    <input type="number" step="0.01" name="ownership_percentage" id="ownership_percentage" class="form-control" value="{{ old('ownership_percentage', 0) }}" required min="0" max="100">
                    @error('ownership_percentage')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="initial_investment" class="form-label">رأس المال المبدئي (ج.م)</label>
                    <input type="number" step="0.01" name="initial_investment" id="initial_investment" class="form-control" value="{{ old('initial_investment', 0) }}" min="0">
                    @error('initial_investment')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="join_date" class="form-label">تاريخ الانضمام</label>
                    <input type="date" name="join_date" id="join_date" class="form-control" value="{{ old('join_date') }}">
                    @error('join_date')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">الحالة</label>
                    <div style="padding-top: 8px;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span>شريك نشط</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">العنوان</label>
                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address') }}</textarea>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">💾 حفظ</button>
                <a href="{{ route('partners.index') }}" class="btn btn-lg">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}
.form-actions {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}
.btn-lg {
    padding: 12px 24px;
    font-size: 1rem;
}
</style>
@endsection
