@extends('layouts.app')

@section('title', 'تعديل منتج')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل منتج: {{ $product->name }}</h1>
        <p>تعديل بيانات المنتج</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('products.index') }}" class="btn">← رجوع للمنتجات</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">اسم المنتج *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sku" class="form-label">كود المنتج (SKU)</label>
                    <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                    @error('sku')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="barcode" class="form-label">الباركود</label>
                    <input type="text" name="barcode" id="barcode" class="form-control" value="{{ old('barcode', $product->barcode) }}">
                    @error('barcode')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="category_id" class="form-label">القسم *</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">اختر القسم</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="unit_id" class="form-label">وحدة القياس</label>
                    <select name="unit_id" id="unit_id" class="form-control">
                        <option value="">اختر الوحدة</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->symbol }})
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">💰 الأسعار</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="cost_price" class="form-label">سعر التكلفة *</label>
                    <input type="number" step="0.01" name="cost_price" id="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price) }}" required>
                    @error('cost_price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="selling_price" class="form-label">سعر البيع *</label>
                    <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control" value="{{ old('selling_price', $product->selling_price) }}" required>
                    @error('selling_price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="min_selling_price" class="form-label">أقل سعر بيع</label>
                    <input type="number" step="0.01" name="min_selling_price" id="min_selling_price" class="form-control" value="{{ old('min_selling_price', $product->min_selling_price) }}">
                    @error('min_selling_price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="wholesale_price" class="form-label">سعر الجملة</label>
                    <input type="number" step="0.01" name="wholesale_price" id="wholesale_price" class="form-control" value="{{ old('wholesale_price', $product->wholesale_price) }}">
                    @error('wholesale_price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">📦 المخزون</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="min_stock" class="form-label">الحد الأدنى للمخزون</label>
                    <input type="number" name="min_stock" id="min_stock" class="form-control" value="{{ old('min_stock', $product->min_stock) }}">
                    @error('min_stock')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="max_stock" class="form-label">الحد الأقصى للمخزون</label>
                    <input type="number" name="max_stock" id="max_stock" class="form-control" value="{{ old('max_stock', $product->max_stock) }}">
                    @error('max_stock')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">⚙️ الإعدادات</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="tax_rate" class="form-label">نسبة الضريبة (%)</label>
                    <input type="number" step="0.01" name="tax_rate" id="tax_rate" class="form-control" value="{{ old('tax_rate', $product->tax_rate) }}" min="0" max="100">
                    @error('tax_rate')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_taxable" value="1" {{ old('is_taxable', $product->is_taxable) ? 'checked' : '' }}>
                        <span>خاضع للضريبة</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="track_inventory" value="1" {{ old('track_inventory', $product->track_inventory) ? 'checked' : '' }}>
                        <span>تتبع المخزون</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <span>نشط</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $product->notes) }}</textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                <a href="{{ route('products.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
