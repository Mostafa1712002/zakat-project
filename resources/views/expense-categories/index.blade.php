@extends('layouts.app')

@section('title', 'أنواع المصروفات')

@section('content')
<div class="page-header">
    <div>
        <h1>📂 أنواع المصروفات</h1>
        <p>إدارة تصنيفات المصروفات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expenses.index') }}" class="btn">← المصروفات</a>
        <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">+ إضافة نوع</a>
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
                    <th>عدد المصروفات</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>
                        <strong>{{ $category->name }}</strong>
                        @if($category->description)
                            <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                        @endif
                    </td>
                    <td>{{ $category->code ?? '-' }}</td>
                    <td>{{ $category->expenses_count }}</td>
                    <td>
                        @if($category->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('expense-categories.edit', $category) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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
                            <div class="empty-state-icon">📂</div>
                            <h3>لا توجد أنواع مصروفات</h3>
                            <p>ابدأ بإضافة أنواع لتصنيف المصروفات</p>
                            <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">+ إضافة نوع</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
    <div class="pagination">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
