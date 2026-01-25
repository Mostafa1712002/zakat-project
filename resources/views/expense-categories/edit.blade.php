@extends('layouts.app')

@section('title', 'تعديل نوع مصروف')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل نوع المصروف</h1>
        <p>{{ $expenseCategory->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expense-categories.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('expense-categories.update', $expenseCategory) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم النوع *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $expenseCategory->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">الكود</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $expenseCategory->code) }}">
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
                        <option value="{{ $parent->id }}" {{ old('parent_id', $expenseCategory->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $expenseCategory->description) }}</textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $expenseCategory->is_active) ? 'checked' : '' }}>
                    <span>نشط</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                <a href="{{ route('expense-categories.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
