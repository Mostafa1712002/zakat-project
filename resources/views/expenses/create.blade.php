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

            {{-- قسم اختيار الموظف --}}
            <div class="form-row employee-section" style="display: none;">
                <div class="form-group">
                    <label for="employee_id" class="form-label">الموظف *</label>
                    <select name="employee_id" id="employee_id" class="form-control">
                        <option value="">-- اختر الموظف --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="transaction_type" class="form-label">نوع المعاملة</label>
                    <select name="transaction_type" id="employee_transaction_type" class="form-control">
                        <option value="salary" {{ old('transaction_type') == 'salary' ? 'selected' : '' }}>مرتب</option>
                        <option value="advance" {{ old('transaction_type') == 'advance' ? 'selected' : '' }}>سلفة</option>
                        <option value="bonus" {{ old('transaction_type') == 'bonus' ? 'selected' : '' }}>مكافأة</option>
                        <option value="deduction" {{ old('transaction_type') == 'deduction' ? 'selected' : '' }}>خصم</option>
                    </select>
                </div>
            </div>

            {{-- قسم اختيار الشريك --}}
            <div class="form-row partner-section" style="display: none;">
                <div class="form-group">
                    <label for="partner_id" class="form-label">الشريك *</label>
                    <select name="partner_id" id="partner_id" class="form-control">
                        <option value="">-- اختر الشريك --</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }} ({{ $partner->ownership_percentage }}%)
                            </option>
                        @endforeach
                    </select>
                    @error('partner_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="partner_transaction_type" class="form-label">نوع المعاملة</label>
                    <select name="transaction_type" id="partner_transaction_type" class="form-control">
                        <option value="withdrawal" {{ old('transaction_type') == 'withdrawal' ? 'selected' : '' }}>سحب</option>
                        <option value="profit_share" {{ old('transaction_type') == 'profit_share' ? 'selected' : '' }}>توزيع أرباح</option>
                    </select>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('expense_category_id');
    const employeeSection = document.querySelector('.employee-section');
    const partnerSection = document.querySelector('.partner-section');
    const employeeTransactionType = document.getElementById('employee_transaction_type');
    const partnerTransactionType = document.getElementById('partner_transaction_type');

    // فئات الموظفين والشركاء (بناءً على الكود)
    const employeeCategories = ['SALARY', 'EMP_ADVANCE'];
    const partnerCategories = ['PARTNER_PROFIT'];

    // ربط الفئات بأنواع المعاملات
    const categoryTransactionMap = {
        'SALARY': 'salary',
        'EMP_ADVANCE': 'advance',
        'PARTNER_PROFIT': 'profit_share'
    };

    // بيانات الفئات
    const categoriesData = @json($categories->mapWithKeys(fn($c) => [$c->id => $c->code]));

    function updateSections() {
        const selectedId = categorySelect.value;
        const categoryCode = categoriesData[selectedId] || '';

        // إخفاء كل الأقسام أولاً
        employeeSection.style.display = 'none';
        partnerSection.style.display = 'none';

        // تعطيل الحقول
        document.getElementById('employee_id').removeAttribute('required');
        document.getElementById('partner_id').removeAttribute('required');

        if (employeeCategories.includes(categoryCode)) {
            employeeSection.style.display = 'flex';
            document.getElementById('employee_id').setAttribute('required', 'required');
            // تحديد نوع المعاملة تلقائياً
            if (categoryTransactionMap[categoryCode]) {
                employeeTransactionType.value = categoryTransactionMap[categoryCode];
            }
        } else if (partnerCategories.includes(categoryCode)) {
            partnerSection.style.display = 'flex';
            document.getElementById('partner_id').setAttribute('required', 'required');
            // تحديد نوع المعاملة تلقائياً
            if (categoryTransactionMap[categoryCode]) {
                partnerTransactionType.value = categoryTransactionMap[categoryCode];
            }
        }
    }

    categorySelect.addEventListener('change', updateSections);
    updateSections(); // تشغيل عند التحميل
});
</script>
@endpush
@endsection
