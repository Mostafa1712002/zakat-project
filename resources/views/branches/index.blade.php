@extends('layouts.app')

@section('title', 'الفروع')

@section('content')
<div class="page-header">
    <div>
        <h1>🏢 الفروع</h1>
        <p>إدارة فروع الشركة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('branches.create') }}" class="btn btn-primary">+ إضافة فرع</a>
    </div>
</div>

<div class="card filter-card">
    <div class="filter-toggle">
        <span class="filter-toggle-title">فلترة وبحث</span>
        <span class="filter-toggle-icon">▼</span>
    </div>
    <div class="filter-body">
        <form action="{{ route('branches.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الكود أو المدير..." value="{{ request('search') }}">
                <select name="is_active" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                </select>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">بحث</button>
                    <a href="{{ route('branches.index') }}" class="btn">إعادة تعيين</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الفرع</th>
                    <th>الكود</th>
                    <th>المدير</th>
                    <th>الهاتف</th>
                    <th>المستخدمين</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td>{{ $branch->id }}</td>
                    <td>
                        <strong>{{ $branch->name }}</strong>
                        @if($branch->is_main)
                            <span class="badge badge-warning">رئيسي</span>
                        @endif
                    </td>
                    <td>{{ $branch->code ?? '-' }}</td>
                    <td>{{ $branch->manager_name ?? '-' }}</td>
                    <td>{{ $branch->phone ?? '-' }}</td>
                    <td><span class="badge badge-primary">{{ $branch->users_count }}</span></td>
                    <td>
                        @if($branch->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('branches.destroy', $branch) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">🏢</div>
                            <h3>لا توجد فروع</h3>
                            <p>ابدأ بإضافة فرع جديد</p>
                            <a href="{{ route('branches.create') }}" class="btn btn-primary">+ إضافة فرع</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($branches->hasPages())
    <div class="card-footer">
        {{ $branches->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
