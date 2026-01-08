@extends('layouts.app')

@section('title', 'المصروفات')

@section('content')
<div class="page-header">
    <div>
        <h1>💸 المصروفات</h1>
        <p>إدارة المصروفات والنفقات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expense-payment-methods.index') }}" class="btn">⚙️ طرق الدفع</a>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">+ إضافة مصروف</a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>البند</th>
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
                    <td colspan="6">
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
@endsection
