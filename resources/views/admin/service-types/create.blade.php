@extends('layouts.app')

@section('title', 'إضافة نوع خدمة')

@section('content')
<div class="page-header">
    <div>
        <h1>🗂️ إضافة نوع خدمة</h1>
        <p>صنف جديد ينتمي إليه مجموعة خدمات</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-right:1rem">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.service-types.store') }}" class="card" style="padding:1.5rem;max-width:720px">
    @csrf

    <div class="form-group">
        <label for="name">الاسم <span style="color:#ef4444">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name') }}"
               class="form-control" maxlength="150" required>
    </div>

    <div class="form-group">
        <label for="icon">الأيقونة</label>
        <input type="text" id="icon" name="icon" value="{{ old('icon') }}"
               class="form-control" maxlength="50" placeholder="fa-building">
        <small>اسم أيقونة FontAwesome أو Lucide (مثال: <code>fa-building</code>)</small>
    </div>

    <div class="form-group">
        <label for="sort_order">الترتيب</label>
        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
               class="form-control" min="0" max="9999">
        <small>الأرقام الأصغر تظهر أولاً</small>
    </div>

    <div class="form-group">
        <label style="display:flex;gap:.5rem;align-items:center">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')>
            <span>نشط</span>
        </label>
    </div>

    <div style="display:flex;gap:.5rem">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('admin.service-types.index') }}" class="btn">إلغاء</a>
    </div>
</form>
@endsection
