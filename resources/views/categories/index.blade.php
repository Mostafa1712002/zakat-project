@extends('layouts.app')

@section('title', 'الأقسام')

@section('content')
<div class="page-header">
    <div>
        <h1>🏷️ الأقسام</h1>
        <p>إدارة أقسام المنتجات والأصناف</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('categories.create') }}" class="btn btn-primary">+ إضافة قسم</a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم القسم</th>
                    <th>الوصف</th>
                    <th>عدد الأصناف</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td class="text-muted">{{ $category->description ?? '-' }}</td>
                    <td><span class="badge badge-primary">{{ $category->products_count }} صنف</span></td>
                    <td>{{ $category->created_at->format('Y/m/d') }}</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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
                            <div class="empty-state-icon">🏷️</div>
                            <h3>لا توجد أقسام</h3>
                            <p>ابدأ بإضافة قسم جديد</p>
                            <a href="{{ route('categories.create') }}" class="btn btn-primary">+ إضافة قسم</a>
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
