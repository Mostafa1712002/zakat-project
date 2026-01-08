@extends('layouts.app')

@section('title', 'إضافة طريقة دفع')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة طريقة دفع</h1>
        <p>إضافة طريقة دفع جديدة للمصروفات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expense-payment-methods.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('expense-payment-methods.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم الطريقة *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">الكود (اختياري)</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="expense_method_code">
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sort_order" class="form-label">الترتيب</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                    @error('sort_order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span>طريقة نشطة</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 حفظ</button>
                <a href="{{ route('expense-payment-methods.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
