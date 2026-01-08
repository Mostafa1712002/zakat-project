@extends('layouts.app')

@section('title', 'تعديل قسم')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل قسم: {{ $category->name }}</h1>
        <p>تعديل بيانات القسم</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('categories.index') }}" class="btn">← رجوع للأقسام</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name" class="form-label">اسم القسم *</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                <a href="{{ route('categories.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
