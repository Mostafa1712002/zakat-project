@extends('layouts.app')

@section('title', 'تعديل مستودع')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل مستودع: {{ $warehouse->name }}</h1>
        <p>تعديل بيانات المستودع</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('warehouses.index') }}" class="btn">← رجوع للمستودعات</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('warehouses.update', $warehouse) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم المستودع *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $warehouse->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">كود المستودع</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $warehouse->code) }}">
                    @error('code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="branch_id" class="form-label">الفرع</label>
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">بدون تخصيص لفرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $warehouse->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="manager_name" class="form-label">مدير المستودع</label>
                    <input type="text" name="manager_name" id="manager_name" class="form-control" value="{{ old('manager_name', $warehouse->manager_name) }}">
                    @error('manager_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $warehouse->phone) }}">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">العنوان</label>
                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $warehouse->address) }}</textarea>
                @error('address')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default', $warehouse->is_default) ? 'checked' : '' }}>
                        <span>مستودع افتراضي</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                        <span>نشط</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $warehouse->notes) }}</textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                <a href="{{ route('warehouses.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
