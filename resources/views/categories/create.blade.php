@extends('layouts.app')

@section('title', 'إضافة قسم')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة قسم جديد</h1>
        <p>أضف قسم جديد لتصنيف المنتجات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('categories.index') }}" class="btn">← رجوع للأقسام</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">اسم القسم *</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="مثال: قطع غيار السيارات">
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="4" placeholder="وصف مختصر للقسم...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ القسم</button>
                <a href="{{ route('categories.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
