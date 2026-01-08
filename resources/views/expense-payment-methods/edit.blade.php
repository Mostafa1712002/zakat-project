@extends('layouts.app')

@section('title', 'تعديل طريقة دفع')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل طريقة دفع</h1>
        <p>تعديل بيانات طريقة الدفع</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expense-payment-methods.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('expense-payment-methods.update', $expensePaymentMethod) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم الطريقة *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $expensePaymentMethod->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">الكود *</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $expensePaymentMethod->code) }}" required>
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sort_order" class="form-label">الترتيب</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $expensePaymentMethod->sort_order) }}" min="0">
                    @error('sort_order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $expensePaymentMethod->is_active) ? 'checked' : '' }}>
                        <span>طريقة نشطة</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                <a href="{{ route('expense-payment-methods.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
