@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@section('content')
<div class="page-header">
    <div>
        <h1>👥 إدارة المستخدمين</h1>
        <p>عرض وإدارة مستخدمي النظام</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('settings.index') }}" class="btn">← رجوع للإعدادات</a>
        <a href="{{ route('settings.users.create') }}" class="btn btn-primary">+ إضافة مستخدم</a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الفرع</th>
                    <th>الدور</th>
                    <th>الحالة</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->branch->name ?? '-' }}</td>
                    <td>
                        @php $roleName = $user->roles->pluck('name')->first(); @endphp
                        <span class="badge badge-primary">{{ $roleName ?? 'بدون دور' }}</span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('Y/m/d') }}</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('settings.users.edit', $user) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('settings.users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>لا يوجد مستخدمين</h3>
                            <p>ابدأ بإضافة مستخدم جديد</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="pagination">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
