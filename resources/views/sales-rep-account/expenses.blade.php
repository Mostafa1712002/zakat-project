@extends('layouts.app')

@section('title', 'مصروفاتي')

@section('content')
<div class="page-header">
    <div>
        <h1>🧾 مصروفاتي</h1>
        <p>تسجيل ومتابعة المصروفات الخاصة بك</p>
    </div>
</div>

<!-- إحصائيات -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">💰</div>
        <div class="stat-details">
            <div class="stat-value">{{ number_format($salesRep->treasury_balance, 2) }} ج.م</div>
            <div class="stat-label">رصيد الخزينة</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">📊</div>
        <div class="stat-details">
            <div class="stat-value">{{ number_format($monthlyTotal, 2) }} ج.م</div>
            <div class="stat-label">مصروفات الشهر</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- نموذج إضافة مصروف -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">➕ تسجيل مصروف جديد</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('sales-rep.expenses.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="expense_category_id" class="form-label">نوع المصروف *</label>
                    <select name="expense_category_id" id="expense_category_id" class="form-control" required>
                        <option value="">-- اختر --</option>
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

                <div class="form-group">
                    <label for="title" class="form-label">الوصف *</label>
                    <input type="text" name="title" id="title" class="form-control"
                           value="{{ old('title') }}" placeholder="مثال: بنزين - تنقلات" required>
                    @error('title')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="amount" class="form-label">المبلغ *</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                               value="{{ old('amount') }}" min="0.01" max="{{ $salesRep->treasury_balance }}" required>
                        <small class="text-muted">الحد الأقصى: {{ number_format($salesRep->treasury_balance, 2) }} ج.م</small>
                        @error('amount')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="expense_date" class="form-label">التاريخ *</label>
                        <input type="date" name="expense_date" id="expense_date" class="form-control"
                               value="{{ old('expense_date', date('Y-m-d')) }}" required>
                        @error('expense_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    💾 تسجيل المصروف
                </button>
            </form>
        </div>
    </div>

    <!-- أنواع المصروفات الشائعة -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💡 أنواع المصروفات</h3>
        </div>
        <div class="card-body">
            <div class="expense-types">
                <div class="expense-type" onclick="setExpenseType('بنزين')">
                    <span class="icon">⛽</span>
                    <span>بنزين</span>
                </div>
                <div class="expense-type" onclick="setExpenseType('صيانة السيارة')">
                    <span class="icon">🔧</span>
                    <span>صيانة</span>
                </div>
                <div class="expense-type" onclick="setExpenseType('أكل وشرب')">
                    <span class="icon">🍽️</span>
                    <span>أكل وشرب</span>
                </div>
                <div class="expense-type" onclick="setExpenseType('مواصلات')">
                    <span class="icon">🚕</span>
                    <span>مواصلات</span>
                </div>
                <div class="expense-type" onclick="setExpenseType('موبايل')">
                    <span class="icon">📱</span>
                    <span>موبايل</span>
                </div>
                <div class="expense-type" onclick="setExpenseType('أخرى')">
                    <span class="icon">📝</span>
                    <span>أخرى</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- قائمة المصروفات -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3 class="card-title">📋 سجل المصروفات</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>الوصف</th>
                    <th>المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date?->format('Y-m-d') }}</td>
                    <td>
                        <span class="badge badge-secondary">{{ $expense->category?->name ?? '-' }}</span>
                    </td>
                    <td>{{ $expense->title }}</td>
                    <td><strong class="text-danger">{{ number_format($expense->total_amount, 2) }} ج.م</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">لا توجد مصروفات مسجلة</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $expenses->links() }}
@endsection

@push('styles')
<style>
.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}
@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
}
.expense-types {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.expense-type {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}
.expense-type:hover {
    background: #e9ecef;
    border-color: var(--primary);
}
.expense-type .icon {
    font-size: 24px;
    margin-bottom: 8px;
}
</style>
@endpush

@push('scripts')
<script>
function setExpenseType(type) {
    document.getElementById('title').value = type;
    document.getElementById('title').focus();
}
</script>
@endpush
