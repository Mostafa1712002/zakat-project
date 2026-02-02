@extends('layouts.app')

@section('title', 'إدارة الأدوار')

@section('content')
<div class="page-header">
    <div>
        <h1>إدارة الأدوار</h1>
        <p>إدارة أدوار المستخدمين والصلاحيات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('settings.roles.create') }}" class="btn btn-primary">+ إضافة دور جديد</a>
        <a href="{{ route('settings.index') }}" class="btn">← رجوع للإعدادات</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الدور</th>
                    <th>عدد المستخدمين</th>
                    <th>عدد الصلاحيات</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $index => $role)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $role->name }}</strong>
                        @if($role->name === 'admin')
                            <span class="badge badge-primary">مدير</span>
                        @elseif($role->name === 'sales_rep')
                            <span class="badge badge-success">مندوب</span>
                        @elseif($role->name === 'employee')
                            <span class="badge badge-warning">موظف</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $role->users_count }} مستخدم</span>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $role->permissions_count }} صلاحية</span>
                    </td>
                    <td>{{ $role->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('settings.roles.edit', $role->id) }}" class="btn btn-sm">تعديل</a>
                            @if($role->users_count == 0)
                            <form action="{{ route('settings.roles.destroy', $role->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الدور؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">لا توجد أدوار</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.badge-primary { background: rgba(8, 145, 178, 0.1); color: #0891b2; }
.badge-success { background: rgba(16, 185, 129, 0.1); color: #059669; }
.badge-warning { background: rgba(245, 158, 11, 0.1); color: #d97706; }
.badge-secondary { background: rgba(100, 116, 139, 0.1); color: #64748b; }
.badge-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.badge-danger { background: rgba(239, 68, 68, 0.1); color: #dc2626; }

.btn-group {
    display: flex;
    gap: 8px;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}
.alert-success { background: #d1fae5; color: #065f46; }
.alert-danger { background: #fee2e2; color: #991b1b; }
</style>
@endsection
