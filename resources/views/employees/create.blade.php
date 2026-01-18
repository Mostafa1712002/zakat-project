@extends('layouts.app')

@section('title', 'إضافة موظف جديد')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة موظف جديد</h1>
        <p>إضافة بيانات موظف جديد</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employees.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<form action="{{ route('employees.store') }}" method="POST">
    @csrf

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
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">كود الموظف</label>
                        <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code') }}" placeholder="سيتم توليده تلقائياً" style="background: rgba(0,0,0,0.05);">
                        <small style="color: #64748b; font-size: 12px;">⚡ يتم توليده تلقائياً إذا تُرك فارغاً</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">الرقم القومي</label>
                        <input type="text" name="national_id" class="form-control" value="{{ old('national_id') }}"
                               pattern="[0-9]{14}" maxlength="14" inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14)"
                               placeholder="أدخل 14 رقم">
                        <small style="color: #64748b; font-size: 12px;">📝 يجب أن يكون 14 رقم فقط</small>
                        @error('national_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">العنوان</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>💼 البيانات الوظيفية</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">المسمى الوظيفي</label>
                        <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">القسم</label>
                        <input type="text" name="department" class="form-control" value="{{ old('department') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-control">
                            <option value="">بدون فرع</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تاريخ التعيين</label>
                        <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', date('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">الراتب</label>
                        <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary') }}" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحالة</label>
                        <select name="is_active" class="form-control">
                            <option value="1" selected>نشط</option>
                            <option value="0">غير نشط</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 حفظ الموظف</button>
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
