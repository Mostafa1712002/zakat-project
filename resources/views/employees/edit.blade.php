@extends('layouts.app')

@section('title', 'تعديل الموظف')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل الموظف</h1>
        <p>{{ $employee->name }} - {{ $employee->employee_code }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employees.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<form action="{{ route('employees.update', $employee) }}" method="POST">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <strong>⚠️ يرجى تصحيح الأخطاء التالية:</strong>
            <ul style="margin: 10px 0 0 0; padding-right: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3>👤 البيانات الشخصية</h3>

                <div class="form-group">
                    <label class="form-label">الاسم *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">كود الموظف</label>
                        <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', $employee->employee_code) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">العنوان</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>💼 البيانات الوظيفية</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">المسمى الوظيفي</label>
                        <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $employee->job_title) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">القسم</label>
                        <input type="text" name="department" class="form-control" value="{{ old('department', $employee->department) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-control">
                            <option value="">بدون فرع</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $employee->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تاريخ التعيين</label>
                        <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">تاريخ إنهاء الخدمة</label>
                        <input type="date" name="termination_date" class="form-control" value="{{ old('termination_date', $employee->termination_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الراتب</label>
                        <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary', $employee->salary) }}" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ $employee->is_active ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ !$employee->is_active ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $employee->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 حفظ التعديلات</button>
            <a href="{{ route('employees.index') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
</style>
@endsection
