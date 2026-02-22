@extends('layouts.app')

@section('title', 'إضافة منتج')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ إضافة منتج جديد</h1>
        <p>أضف منتج جديد للمخزون</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('products.index') }}" class="btn">← رجوع للمنتجات</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group" style="position: relative;">
                    <label for="name" class="form-label">اسم المنتج *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="اسم المنتج" autocomplete="off">
                    <div id="nameSuggestions" class="suggestions-dropdown"></div>
                    <div id="nameWarning" class="form-warning" style="display: none;">
                        ⚠️ يوجد منتج بنفس الاسم أو اسم مشابه
                    </div>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sku" class="form-label">كود المنتج (SKU)</label>
                    <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku') }}" placeholder="سيتم توليده تلقائياً" style="background: rgba(0,0,0,0.05);">
                    <small style="color: #64748b; font-size: 12px;">⚡ يتم توليده تلقائياً إذا تُرك فارغاً</small>
                    @error('sku')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id" class="form-label">القسم *</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">اختر القسم</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->symbol }})
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="supplier_ids" class="form-label">الموردين</label>
                    <select name="supplier_ids[]" id="supplier_ids" class="form-control" multiple>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ collect(old('supplier_ids', []))->contains($supplier->id) ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #64748b; font-size: 12px;">اختر مورد أو أكثر (اختياري)</small>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <textarea name="description" id="description" class="form-control" rows="3" placeholder="وصف المنتج...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">💰 الأسعار</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="cost_price" class="form-label">سعر التكلفة *</label>
                    <input type="number" step="0.01" name="cost_price" id="cost_price" class="form-control" value="{{ old('cost_price', 0) }}" required>
                    @error('cost_price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="selling_price" class="form-label">سعر البيع *</label>
                    <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control" value="{{ old('selling_price', 0) }}" required>
                    @error('selling_price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="min_selling_price" class="form-label">أقل سعر بيع</label>
                    <input type="number" step="0.01" name="min_selling_price" id="min_selling_price" class="form-control" value="{{ old('min_selling_price') }}">
                    @error('min_selling_price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="wholesale_price" class="form-label">سعر الجملة</label>
                    <input type="number" step="0.01" name="wholesale_price" id="wholesale_price" class="form-control" value="{{ old('wholesale_price') }}">
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
                    <input type="number" name="min_stock" id="min_stock" class="form-control" value="{{ old('min_stock', 0) }}">
                    @error('min_stock')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="max_stock" class="form-label">الحد الأقصى للمخزون</label>
                    <input type="number" name="max_stock" id="max_stock" class="form-control" value="{{ old('max_stock') }}">
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
                    <input type="number" step="0.01" name="tax_rate" id="tax_rate" class="form-control" value="{{ old('tax_rate', 0) }}" min="0" max="100">
                    @error('tax_rate')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_taxable" value="1" {{ old('is_taxable', true) ? 'checked' : '' }}>
                        <span>خاضع للضريبة</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="track_inventory" value="1" {{ old('track_inventory', true) ? 'checked' : '' }}>
                        <span>تتبع المخزون</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span>نشط</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="ملاحظات إضافية...">{{ old('notes') }}</textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ المنتج</button>
                <a href="{{ route('products.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid var(--border);
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 100;
    display: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.suggestions-dropdown.show { display: block; }
.suggestion-item {
    padding: 10px 16px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.suggestion-item:last-child { border-bottom: none; }
.suggestion-item:hover { background: var(--bg); }
.suggestion-item .name { font-weight: 500; }
.suggestion-item .sku { font-size: 12px; color: var(--text-muted); }
.suggestion-item.exact-match { background: #fef3c7; }
.form-warning {
    color: #b45309;
    background: #fef3c7;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    margin-top: 6px;
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    $('#supplier_ids').select2({ placeholder: 'اختر الموردين...', allowClear: true, dir: 'rtl', width: '100%' });
});
const existingProducts = @json($existingProducts);
const nameInput = document.getElementById('name');
const suggestionsDiv = document.getElementById('nameSuggestions');
const warningDiv = document.getElementById('nameWarning');

nameInput.addEventListener('input', function() {
    const value = this.value.trim().toLowerCase();

    if (value.length < 2) {
        suggestionsDiv.classList.remove('show');
        warningDiv.style.display = 'none';
        return;
    }

    const matches = existingProducts.filter(p =>
        p.name.toLowerCase().includes(value)
    ).slice(0, 10);

    if (matches.length > 0) {
        let html = '';
        let hasExactMatch = false;

        matches.forEach(p => {
            const isExact = p.name.toLowerCase() === value;
            if (isExact) hasExactMatch = true;
            html += `<div class="suggestion-item ${isExact ? 'exact-match' : ''}" data-name="${p.name}">
                <span class="name">${p.name}</span>
                <span class="sku">${p.sku || ''}</span>
            </div>`;
        });

        suggestionsDiv.innerHTML = html;
        suggestionsDiv.classList.add('show');
        warningDiv.style.display = hasExactMatch ? 'block' : 'none';

        // Click to select
        suggestionsDiv.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                nameInput.value = this.dataset.name;
                suggestionsDiv.classList.remove('show');
                warningDiv.style.display = 'block';
            });
        });
    } else {
        suggestionsDiv.classList.remove('show');
        warningDiv.style.display = 'none';
    }
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!nameInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
        suggestionsDiv.classList.remove('show');
    }
});

// Hide suggestions on blur (with delay for click to work)
nameInput.addEventListener('blur', function() {
    setTimeout(() => suggestionsDiv.classList.remove('show'), 200);
});
</script>
@endpush
@endsection
