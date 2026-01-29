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
                    <label for="expense_category_id" class="form-label">نوع المصروف</label>
                    <select name="expense_category_id" id="expense_category_id" class="form-control">
                        <option value="">-- اختر النوع --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('expense_category_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
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

            <!-- ربط بمندوب -->
            <div class="form-row">
                <div class="form-group">
                    <label for="sales_rep_id" class="form-label">المندوب (اختياري)</label>
                    <select name="sales_rep_id" id="sales_rep_id" class="form-control" onchange="toggleTreasuryOption()">
                        <option value="">-- بدون مندوب --</option>
                        @foreach($salesReps as $rep)
                            <option value="{{ $rep->id }}" data-balance="{{ $rep->treasury_balance }}" {{ old('sales_rep_id') == $rep->id ? 'selected' : '' }}>
                                {{ $rep->name }} (الخزينة: {{ number_format($rep->treasury_balance) }} ج.م)
                            </option>
                        @endforeach
                    </select>
                    @error('sales_rep_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="treasuryOption" style="display: none;">
                    <label class="form-label">&nbsp;</label>
                    <label class="checkbox-item" style="margin-top: 8px;">
                        <input type="checkbox" name="deduct_from_treasury" value="1" {{ old('deduct_from_treasury') ? 'checked' : '' }}>
                        <span>خصم من خزينة المندوب</span>
                    </label>
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

<style>
.checkbox-item { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
.checkbox-item input[type="checkbox"] { width: 18px; height: 18px; }
</style>

<script>
function toggleTreasuryOption() {
    const salesRepSelect = document.getElementById('sales_rep_id');
    const treasuryOption = document.getElementById('treasuryOption');

    if (salesRepSelect.value) {
        treasuryOption.style.display = 'block';
    } else {
        treasuryOption.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', toggleTreasuryOption);
</script>
@endsection
