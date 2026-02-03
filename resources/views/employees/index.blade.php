@extends('layouts.app')

@section('title', 'الموظفين')

@section('content')
<div class="page-header">
    <div>
        <h1>👨‍💼 الموظفين</h1>
        <p>إدارة بيانات الموظفين</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employees.create') }}" class="btn btn-primary">+ إضافة موظف</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('employees.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الكود أو الهاتف..." value="{{ request('search') }}">
                <select name="department" class="form-control">
                    <option value="">كل الأقسام</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <select name="is_active" class="form-control">
                    <option value="">الكل</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                </select>
                <button type="submit" class="btn btn-primary">🔍 بحث</button>
                <a href="{{ route('employees.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container  overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الاسم</th>
                    <th>الوظيفة</th>
                    <th>القسم</th>
                    <th>الهاتف</th>
                    <th>الفرع</th>
                    <th>تاريخ التعيين</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td><code>{{ $employee->employee_code }}</code></td>
                    <td><strong>{{ $employee->name }}</strong></td>
                    <td>{{ $employee->job_title ?? '-' }}</td>
                    <td>{{ $employee->department ?? '-' }}</td>
                    <td>{{ $employee->phone ?? '-' }}</td>
                    <td>{{ $employee->branch->name ?? '-' }}</td>
                    <td>{{ $employee->hire_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>
                        @if($employee->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm" title="عرض">👁️</a>
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm" title="تعديل">✏️</a>
                            <form action="{{ route('employees.destroy', $employee) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">👨‍💼</div>
                            <h3>لا يوجد موظفين</h3>
                            <p>ابدأ بإضافة موظف جديد</p>
                            <a href="{{ route('employees.create') }}" class="btn btn-primary">+ إضافة موظف</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($employees->hasPages())
    <div class="card-footer">
        {{ $employees->withQueryString()->links() }}
    </div>
    @endif
</div>

<style>
.filter-form { margin-bottom: 0; }
.filter-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
.filter-row .form-control { flex: 1; min-width: 150px; }
.filter-row .btn { white-space: nowrap; }
.table-actions { display: flex; gap: 0.25rem; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-success { background: #10b981; color: white; }
.badge-danger { background: #ef4444; color: white; }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>
@endsection
