@extends('layouts.app')

@section('title', 'طرق دفع المصروفات')

@section('content')
<div class="page-header">
    <div>
        <h1>💳 طرق دفع المصروفات</h1>
        <p>إدارة طرق الدفع المستخدمة في المصروفات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expense-payment-methods.create') }}" class="btn btn-primary">+ إضافة طريقة</a>
        <a href="{{ route('expenses.index') }}" class="btn">← المصروفات</a>
    </div>
</div>

<div class="card">
    <div class="table-container  overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الكود</th>
                    <th>الحالة</th>
                    <th>الترتيب</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($methods as $method)
                <tr>
                    <td>{{ $method->id }}</td>
                    <td><strong>{{ $method->name }}</strong></td>
                    <td class="text-muted">{{ $method->code }}</td>
                    <td>
                        @if($method->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>{{ $method->sort_order }}</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('expense-payment-methods.edit', $method) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('expense-payment-methods.destroy', $method) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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
                            <div class="empty-state-icon">💳</div>
                            <h3>لا توجد طرق دفع</h3>
                            <p>ابدأ بإضافة طريقة دفع جديدة</p>
                            <a href="{{ route('expense-payment-methods.create') }}" class="btn btn-primary">+ إضافة طريقة</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($methods->hasPages())
    <div class="pagination">
        {{ $methods->links() }}
    </div>
    @endif
</div>
@endsection
