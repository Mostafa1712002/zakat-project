@extends('layouts.app')

@section('title', 'تحويل مخزون')

@section('content')
<div class="page-header">
    <div>
        <h1>🔄 تحويل مخزون</h1>
        <p>نقل الأصناف بين المخازن</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('warehouses.index') }}" class="btn">← رجوع للمخازن</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('warehouses.process-transfer') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">الصنف *</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">اختر الصنف</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">الكمية *</label>
                    <input type="number" name="quantity" class="form-control" min="0.001" step="0.001" value="{{ old('quantity', 1) }}" required>
                    @error('quantity')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">من مخزن *</label>
                    <select name="from_warehouse_id" class="form-control" required>
                        <option value="">اختر المخزن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">إلى مخزن *</label>
                    <select name="to_warehouse_id" class="form-control" required>
                        <option value="">اختر المخزن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="سبب التحويل أو أي ملاحظات...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">🔄 تنفيذ التحويل</button>
                <a href="{{ route('warehouses.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
</style>
@endsection
