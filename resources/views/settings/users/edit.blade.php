@extends('layouts.app')

@section('title', 'تعديل المستخدم')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل المستخدم</h1>
        <p>{{ $user->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('settings.users') }}" class="btn">← رجوع للمستخدمين</a>
    </div>
</div>

<form action="{{ route('settings.users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3>👤 البيانات الأساسية</h3>

                <div class="form-group">
                    <label class="form-label">الاسم *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control">
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-control">
                            <option value="">بدون فرع</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ old('is_active', $user->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>🔐 الصلاحيات</h3>

                <div class="form-group">
                    <label class="form-label">الأدوار</label>
                    <div class="checkbox-grid">
                        @foreach($roles as $role)
                            @php
                                $selectedRoles = old('roles', $user->roles->pluck('name')->toArray());
                            @endphp
                            <label class="checkbox-item">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ in_array($role->name, $selectedRoles) ? 'checked' : '' }}>
                                <span>{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 تحديث المستخدم</button>
            <a href="{{ route('settings.users') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.checkbox-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; }
.checkbox-item { display: flex; align-items: center; gap: 0.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
</style>
@endsection
