@extends('layouts.app')

@section('title', 'تسجيل معاملة موظف')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ تسجيل معاملة جديدة</h1>
        <p>تسجيل مرتب أو سلفة أو مكافأة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employee-transactions.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('employee-transactions.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="employee_id" class="form-label">الموظف *</label>
                    <select name="employee_id" id="employee_id" class="form-control" required>
                        <option value="">-- اختر الموظف --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                data-salary="{{ $employee->salary }}"
                                {{ old('employee_id', $selectedEmployee) == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} {{ $employee->employee_code ? '(' . $employee->employee_code . ')' : '' }}
                                @if($employee->salary) - المرتب: {{ number_format($employee->salary, 2) }} @endif
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
                        <option value="salary" {{ old('type', $selectedType) == 'salary' ? 'selected' : '' }}>💵 مرتب</option>
                        <option value="advance" {{ old('type', $selectedType) == 'advance' ? 'selected' : '' }}>🏦 سلفة</option>
                        <option value="bonus" {{ old('type', $selectedType) == 'bonus' ? 'selected' : '' }}>🎁 مكافأة</option>
                        <option value="deduction" {{ old('type', $selectedType) == 'deduction' ? 'selected' : '' }}>➖ خصم</option>
                    </select>
                    @error('type')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="amount" class="form-label">المبلغ (ج.م) *</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required min="0.01">
                    @error('amount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="transaction_date" class="form-label">تاريخ المعاملة *</label>
                    <input type="date" name="transaction_date" id="transaction_date" class="form-control" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                    @error('transaction_date')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" id="month_year_group">
                    <label for="month_year" class="form-label">شهر المرتب</label>
                    <input type="month" name="month_year" id="month_year" class="form-control" value="{{ old('month_year', date('Y-m')) }}">
                    <small class="form-help">للمرتبات: حدد الشهر الذي يتم صرف المرتب عنه</small>
                    @error('month_year')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="payment_method" class="form-label">طريقة الدفع *</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 نقدي</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>🏦 تحويل بنكي</option>
                        <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>📝 شيك</option>
                    </select>
                    @error('payment_method')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="reference_number" class="form-label">رقم المرجع</label>
                <input type="text" name="reference_number" id="reference_number" class="form-control" value="{{ old('reference_number') }}" placeholder="رقم الإيصال أو التحويل">
                @error('reference_number')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="2" placeholder="وصف المعاملة...">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="ملاحظات إضافية...">{{ old('notes') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">💾 حفظ</button>
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
.form-help {
    display: block;
    color: var(--text-muted);
    font-size: 0.75rem;
    margin-top: 0.25rem;
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
    const employeeSelect = document.getElementById('employee_id');
    const amountInput = document.getElementById('amount');

    function toggleMonthYear() {
        monthYearGroup.style.display = typeSelect.value === 'salary' ? 'block' : 'none';
    }

    // Auto-fill salary amount when employee is selected and type is salary
    function autoFillSalary() {
        if (typeSelect.value === 'salary') {
            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            const salary = selectedOption.dataset.salary;
            if (salary && !amountInput.value) {
                amountInput.value = salary;
            }
        }
    }

    typeSelect.addEventListener('change', toggleMonthYear);
    employeeSelect.addEventListener('change', autoFillSalary);
    typeSelect.addEventListener('change', autoFillSalary);

    toggleMonthYear();
});
</script>
@endsection
