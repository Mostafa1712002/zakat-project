@extends('layouts.app')

@section('title', 'المصروفات')

@section('content')
<div class="page-header">
    <div>
        <h1>💸 المصروفات</h1>
        <p>إدارة المصروفات والنفقات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expense-categories.index') }}" class="btn">📂 أنواع المصروفات</a>
        <a href="{{ route('expense-payment-methods.index') }}" class="btn">⚙️ طرق الدفع</a>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">+ إضافة مصروف</a>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form action="{{ route('expenses.index') }}" method="GET" class="filters-form">
            <div class="form-row" style="align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">نوع المصروف</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- الكل --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <button type="submit" class="btn btn-primary">🔍 بحث</button>
                    <a href="{{ route('expenses.index') }}" class="btn">إلغاء</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container  overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>البند</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                    <th>ملاحظات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->id }}</td>
                    <td><strong>{{ $expense->title }}</strong></td>
                    <td>
                        @if($expense->category)
                            <span class="badge badge-primary">{{ $expense->category->name }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-danger">{{ number_format($expense->amount, 2) }} ج.م</td>
                    <td>{{ $expense->expense_date?->format('Y/m/d') ?? $expense->created_at->format('Y/m/d') }}</td>
                    <td class="text-muted">{{ Str::limit($expense->notes, 50) ?? '-' }}</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm">عرض</a>
                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">💸</div>
                            <h3>لا توجد مصروفات</h3>
                            <p>ابدأ بتسجيل مصروف جديد</p>
                            <a href="{{ route('expenses.create') }}" class="btn btn-primary">+ إضافة مصروف</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
    <div class="pagination">
        {{ $expenses->links() }}
    </div>
    @endif
</div>

<style>
.filters-form .form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}
.filters-form .form-group {
    flex: 1;
    min-width: 150px;
}
@media (max-width: 768px) {
    .filters-form .form-group {
        flex: 100%;
    }
}
</style>
@endsection
