@extends('layouts.app')

@section('title', 'تعديل فرع')

@section('content')
<div class="page-header">
    <div>
        <h1>تعديل فرع: {{ $branch->name }}</h1>
        <p>تعديل بيانات الفرع</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('branches.index') }}" class="btn">رجوع</a>
    </div>
</div>

<form action="{{ route('branches.update', $branch) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">بيانات الفرع</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم الفرع *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $branch->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">كود الفرع</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $branch->code) }}">
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="manager_name" class="form-label">اسم المدير</label>
                    <input type="text" name="manager_name" id="manager_name" class="form-control" value="{{ old('manager_name', $branch->manager_name) }}">
                    @error('manager_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $branch->phone) }}">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $branch->email) }}">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">العنوان</label>
                    <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $branch->address) }}">
                    @error('address')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="is_active" class="form-label">الحالة</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $branch->is_active) == 1 ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ old('is_active', $branch->is_active) == 0 ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="is_main" class="form-label">فرع رئيسي؟</label>
                    <select name="is_main" id="is_main" class="form-control">
                        <option value="0" {{ old('is_main', $branch->is_main) == 0 ? 'selected' : '' }}>لا</option>
                        <option value="1" {{ old('is_main', $branch->is_main) == 1 ? 'selected' : '' }}>نعم</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $branch->notes) }}</textarea>
                @error('notes')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
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
