@extends('layouts.app')

@section('title', 'الموظفين')

@section('content')
<div class="page-header">
    <div>
        <h1>الموظفين</h1>
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

<div class="card filter-card">
    <div class="filter-toggle">
        <span class="filter-toggle-title">فلترة وبحث</span>
        <span class="filter-toggle-icon">▼</span>
    </div>
    <div class="filter-body">
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
                    <option value="">كل الحالات</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                </select>
                <select name="has_account" class="form-control">
                    <option value="">الكل</option>
                    <option value="1" {{ request('has_account') === '1' ? 'selected' : '' }}>لديه حساب</option>
                    <option value="0" {{ request('has_account') === '0' ? 'selected' : '' }}>بدون حساب</option>
                </select>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">بحث</button>
                    <a href="{{ route('employees.index') }}" class="btn">إعادة تعيين</a>
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
                    <th>الكود</th>
                    <th>الاسم</th>
                    <th>الوظيفة</th>
                    <th>القسم</th>
                    <th>الهاتف</th>
                    <th>الفرع</th>
                    <th>الحساب</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td><code>{{ $employee->employee_code }}</code></td>
                    <td>
                        <strong>{{ $employee->name }}</strong>
                        @if($employee->hire_date)
                            <br><small class="text-muted">{{ $employee->hire_date->format('Y-m-d') }}</small>
                        @endif
                    </td>
                    <td>{{ $employee->job_title ?? '-' }}</td>
                    <td>{{ $employee->department ?? '-' }}</td>
                    <td>{{ $employee->phone ?? '-' }}</td>
                    <td>{{ $employee->branch->name ?? '-' }}</td>
                    <td>
                        @if($employee->user_id)
                            <span class="badge badge-primary" title="{{ $employee->user?->email }}">لديه حساب</span>
                        @else
                            <span class="badge badge-secondary">بدون حساب</span>
                        @endif
                    </td>
                    <td>
                        @if($employee->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm" title="عرض">عرض</a>
                            <a href="{{ route('employee-transactions.employee-history', $employee) }}" class="btn btn-sm" title="المعاملات">المعاملات</a>
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm" title="تعديل">تعديل</a>
                            <form action="{{ route('employees.destroy', $employee) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">حذف</button>
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
@endsection
