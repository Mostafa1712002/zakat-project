@extends('layouts.app')

@section('title', 'تعديل معاملة موظف')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل معاملة</h1>
        <p>{{ $employeeTransaction->transaction_number }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employee-transactions.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('employee-transactions.update', $employeeTransaction) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="employee_id" class="form-label">الموظف *</label>
                    <select name="employee_id" id="employee_id" class="form-control" required>
                        <option value="">-- اختر الموظف --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                data-salary="{{ $employee->salary }}"
                                {{ old('employee_id', $employeeTransaction->employee_id) == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} {{ $employee->employee_code ? '(' . $employee->employee_code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type" class="form-label">نوع المعاملة *</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="salary" {{ old('type', $employeeTransaction->type) == 'salary' ? 'selected' : '' }}>💵 مرتب</option>
                        <option value="advance" {{ old('type', $employeeTransaction->type) == 'advance' ? 'selected' : '' }}>🏦 سلفة</option>
                        <option value="bonus" {{ old('type', $employeeTransaction->type) == 'bonus' ? 'selected' : '' }}>🎁 مكافأة</option>
                        <option value="deduction" {{ old('type', $employeeTransaction->type) == 'deduction' ? 'selected' : '' }}>➖ خصم</option>
                    </select>
                    @error('type')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="amount" class="form-label">المبلغ (ج.م) *</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount', $employeeTransaction->amount) }}" required min="0.01">
                    @error('amount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="transaction_date" class="form-label">تاريخ المعاملة *</label>
                    <input type="date" name="transaction_date" id="transaction_date" class="form-control" value="{{ old('transaction_date', $employeeTransaction->transaction_date->format('Y-m-d')) }}" required>
                    @error('transaction_date')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" id="month_year_group">
                    <label for="month_year" class="form-label">شهر المرتب</label>
                    <input type="month" name="month_year" id="month_year" class="form-control" value="{{ old('month_year', $employeeTransaction->month_year) }}">
                    @error('month_year')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="payment_method" class="form-label">طريقة الدفع *</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="cash" {{ old('payment_method', $employeeTransaction->payment_method) == 'cash' ? 'selected' : '' }}>💵 نقدي</option>
                        <option value="bank_transfer" {{ old('payment_method', $employeeTransaction->payment_method) == 'bank_transfer' ? 'selected' : '' }}>🏦 تحويل بنكي</option>
                        <option value="check" {{ old('payment_method', $employeeTransaction->payment_method) == 'check' ? 'selected' : '' }}>📝 شيك</option>
                    </select>
                    @error('payment_method')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="reference_number" class="form-label">رقم المرجع</label>
                <input type="text" name="reference_number" id="reference_number" class="form-control" value="{{ old('reference_number', $employeeTransaction->reference_number) }}">
                @error('reference_number')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $employeeTransaction->description) }}</textarea>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $employeeTransaction->notes) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">💾 حفظ التعديلات</button>
                <a href="{{ route('employee-transactions.index') }}" class="btn btn-lg">إلغاء</a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const monthYearGroup = document.getElementById('month_year_group');

    function toggleMonthYear() {
        monthYearGroup.style.display = typeSelect.value === 'salary' ? 'block' : 'none';
    }

    typeSelect.addEventListener('change', toggleMonthYear);
    toggleMonthYear();
});
</script>
@endsection
