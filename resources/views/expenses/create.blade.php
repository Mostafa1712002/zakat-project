@extends('layouts.app')

@section('title', 'إضافة مصروف')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة مصروف جديد</h1>
        <p>تسجيل مصروف جديد</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expense-payment-methods.index') }}" class="btn">⚙️ طرق الدفع</a>
        <a href="{{ route('expenses.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="title" class="form-label">عنوان المصروف *</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="expense_date" class="form-label">التاريخ *</label>
                    <input type="date" name="expense_date" id="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="amount" class="form-label">المبلغ (ج.م) *</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required min="0">
                    @error('amount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="expense_payment_method_id" class="form-label">طريقة الدفع</label>
                    <select name="expense_payment_method_id" id="expense_payment_method_id" class="form-control">
                        <option value="">اختر طريقة الدفع</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}" {{ old('expense_payment_method_id') == $method->id ? 'selected' : '' }}>
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('expense_payment_method_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="vendor_name" class="form-label">اسم المورد/الجهة</label>
                    <input type="text" name="vendor_name" id="vendor_name" class="form-control" value="{{ old('vendor_name') }}">
                </div>

                <div class="form-group">
                    <label for="reference_number" class="form-label">رقم المرجع</label>
                    <input type="text" name="reference_number" id="reference_number" class="form-control" value="{{ old('reference_number') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 حفظ</button>
                <a href="{{ route('expenses.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
