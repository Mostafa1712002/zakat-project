@extends('layouts.app')

@section('title', 'إضافة نوع مصروف')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة نوع مصروف</h1>
        <p>إضافة تصنيف جديد للمصروفات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expense-categories.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('expense-categories.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم النوع *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="مثال: مرتبات، سلف، مصروفات عامة">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">الكود</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="مثال: SAL, ADV, GEN">
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="parent_id" class="form-label">النوع الأب (اختياري)</label>
                <select name="parent_id" id="parent_id" class="form-control">
                    <option value="">-- بدون (نوع رئيسي) --</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="2">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span>نشط</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 حفظ</button>
                <a href="{{ route('expense-categories.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
