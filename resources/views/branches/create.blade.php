@extends('layouts.app')

@section('title', 'إضافة فرع')

@section('content')
<div class="page-header">
    <div>
        <h1>إضافة فرع جديد</h1>
        <p>إضافة فرع جديد للشركة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('branches.index') }}" class="btn">رجوع</a>
    </div>
</div>

<form action="{{ route('branches.store') }}" method="POST">
    @csrf

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">بيانات الفرع</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم الفرع *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="مثال: الفرع الرئيسي">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">كود الفرع</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="سيتم توليده تلقائياً">
                    <small class="text-muted">يتم توليده تلقائياً إذا تُرك فارغاً</small>
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="manager_name" class="form-label">اسم المدير</label>
                    <input type="text" name="manager_name" id="manager_name" class="form-control" value="{{ old('manager_name') }}">
                    @error('manager_name')
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
                    <label for="address" class="form-label">العنوان</label>
                    <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}">
                    @error('address')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="is_active" class="form-label">الحالة</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="is_main" class="form-label">فرع رئيسي؟</label>
                    <select name="is_main" id="is_main" class="form-control">
                        <option value="0" {{ old('is_main', 0) == 0 ? 'selected' : '' }}>لا</option>
                        <option value="1" {{ old('is_main') == 1 ? 'selected' : '' }}>نعم</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('branches.index') }}" class="btn">إلغاء</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 16px;
    }
    @media (max-width: 768px) {
        .form-row { grid-template-columns: 1fr; }
    }
</style>
@endpush
