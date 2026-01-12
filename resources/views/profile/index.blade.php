@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
<div class="page-header">
    <h1 class="page-title">👤 الملف الشخصي</h1>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
    <!-- معلومات الحساب -->
    <div class="card">
        <div class="card-header">
            <h3>📝 معلومات الحساب</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">الاسم</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    @error('name')
                        <span style="color: #f87171; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    @error('email')
                        <span style="color: #f87171; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                           class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    @error('phone')
                        <span style="color: #f87171; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">نوع الحساب</label>
                    <input type="text" value="{{ $user->is_super_admin ? 'مسؤول أعلى' : 'مستخدم عادي' }}" 
                           class="form-control" disabled style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: #94a3b8;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">تاريخ الإنشاء</label>
                    <input type="text" value="{{ $user->created_at->format('Y-m-d H:i') }}" 
                           class="form-control" disabled style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: #94a3b8;">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); border: none; border-radius: 8px; color: #fff; cursor: pointer; font-size: 16px;">
                    💾 حفظ التغييرات
                </button>
            </form>
        </div>
    </div>

    <!-- تغيير كلمة المرور -->
    <div class="card">
        <div class="card-header">
            <h3>🔐 تغيير كلمة المرور</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">كلمة المرور الحالية</label>
                    <input type="password" name="current_password" 
                           class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    @error('current_password')
                        <span style="color: #f87171; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">كلمة المرور الجديدة</label>
                    <input type="password" name="password" 
                           class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    @error('password')
                        <span style="color: #f87171; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" name="password_confirmation" 
                           class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                </div>

                <button type="submit" class="btn btn-warning" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #f59e0b, #ef4444); border: none; border-radius: 8px; color: #fff; cursor: pointer; font-size: 16px;">
                    🔑 تغيير كلمة المرور
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
