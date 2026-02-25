@extends('layouts.app')

@section('title', 'تسعيرة مبيعات جديدة')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ تسعيرة مبيعات جديدة</h1>
        <p>إنشاء عرض سعر جديد (بدون خصم مخزون أو دفع)</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('quotations.index') }}" class="btn">← رجوع للتسعيرات</a>
    </div>
</div>

<form action="{{ route('quotations.store') }}" method="POST" id="quotationForm">
    @csrf

    <div class="grid-2">
        <!-- Customer & Invoice Info -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">📋 بيانات التسعيرة</h3>

                <div class="form-group">
                    <label for="customer_id" class="form-label">العميل *</label>
                    <select name="customer_id" id="customer_id" class="form-control" required>
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} {{ $customer->phone ? '- ' . $customer->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_date" class="form-label">تاريخ التسعيرة *</label>
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="due_date" class="form-label">صالحة حتى</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_type" class="form-label">نوع الدفع *</label>
                        <select name="payment_type" id="payment_type" class="form-control" required>
                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>نقدي</option>
                            <option value="credit" {{ old('payment_type') == 'credit' ? 'selected' : '' }}>آجل</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="warehouse_id" class="form-label">المستودع *</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id || $warehouse->is_default ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="branch_id" class="form-label">الفرع</label>
                        <select name="branch_id" id="branch_id" class="form-control">
                            <option value="">اختر الفرع</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sales_rep_id" class="form-label">المندوب</label>
                        <select name="sales_rep_id" id="sales_rep_id" class="form-control">
                            <option value="">بدون مندوب</option>
                            @foreach($salesReps as $rep)
                                <option value="{{ $rep->id }}" {{ old('sales_rep_id') == $rep->id ? 'selected' : '' }}>
                                    {{ $rep->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Discount -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">💰 الخصم</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="discount_type" class="form-label">نوع الخصم</label>
                        <select name="discount_type" id="discount_type" class="form-control">
                            <option value="fixed">مبلغ ثابت</option>
                            <option value="percentage">نسبة مئوية</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="discount_value" class="form-label">قيمة الخصم</label>
                        <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control" value="{{ old('discount_value', 0) }}" min="0">
                    </div>
                </div>

                <div class="quotation-note" style="margin-top: 16px;">
                    <div style="padding: 12px; background: #fefce8; border: 1px solid #fde68a; border-radius: 8px; font-size: 0.85rem;">
                        <strong>📋 ملاحظة:</strong> التسعيرة لا تخصم من المخزون ولا تنشئ دفعة. يمكنك تحويلها لفاتورة مبيعات حقيقية لاحقاً.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">🛒 أصناف التسعيرة</h3>

            <div class="table-container overflow-auto">
                <table class="table text-nowrap" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 30%;">الصنف</th>
                            @if(feature_enabled('grade_system'))
                            <th style="width: 10%;">الفرز</th>
                            @endif
                            <th style="width: 12%;">الكمية</th>
                            <th style="width: 12%;">سعر الوحدة</th>
                            @if(feature_enabled('per_item_discount'))
                            <th style="width: 10%;">الخصم</th>
                            @endif
                            @if(feature_enabled('tile_area_tracking'))
                            <th style="width: 10%;">المساحة</th>
                            @endif
                            <th style="width: 12%;">الإجمالي</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row" data-index="0">
                            <td>
                                <select name="items[0][product_id]" class="form-control product-select" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @unless(feature_enabled('per_item_discount'))
                                <input type="hidden" name="items[0][discount_amount]" class="discount-input" value="0">
                                @endunless
                            </td>
                            @if(feature_enabled('grade_system'))
                            <td>
                                <select name="items[0][grade_id]" class="form-control grade-select" style="font-size: 12px; padding: 6px;">
                                    <option value="">-</option>
                                    @foreach($grades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name_ar ?? $grade->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            @endif
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control quantity-input" value="1" min="0.001" step="0.001" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][unit_price]" class="form-control price-input" value="0" min="0" step="0.01" required>
                            </td>
                            @if(feature_enabled('per_item_discount'))
                            <td>
                                <input type="number" name="items[0][discount_amount]" class="form-control discount-input" value="0" min="0" step="0.01">
                            </td>
                            @endif
                            @if(feature_enabled('tile_area_tracking'))
                            <td>
                                <span class="row-area">-</span> م²
                            </td>
                            @endif
                            <td>
                                <span class="row-total">0.00</span> ج.م
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-row" style="display: none;">×</button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            @php
                                $colCount = 5;
                                if (feature_enabled('per_item_discount')) $colCount++;
                                if (feature_enabled('grade_system')) $colCount++;
                                if (feature_enabled('tile_area_tracking')) $colCount++;
                            @endphp
                            <td colspan="{{ $colCount }}">
                                <button type="button" class="btn btn-sm" id="addRowBtn">+ إضافة صنف</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-row">
                    <span>الإجمالي الفرعي:</span>
                    <strong id="subtotal">0.00</strong> ج.م
                </div>
                <div class="totals-row">
                    <span>الخصم:</span>
                    <strong id="totalDiscount">0.00</strong> ج.م
                </div>
                <div class="totals-row total-final">
                    <span>الإجمالي النهائي:</span>
                    <strong id="grandTotal">0.00</strong> ج.م
                </div>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 حفظ التسعيرة</button>
            <a href="{{ route('quotations.index') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 1.5rem; }
.totals-section { margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid var(--border); max-width: 300px; margin-right: auto; }
.totals-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.total-final { font-size: 1.25rem; border-top: 2px solid var(--primary); padding-top: 1rem; margin-top: 0.5rem; }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
@media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; gap: 1rem; } .totals-section { max-width: 100%; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;

    const productsData = {!! json_encode($products->mapWithKeys(function($p) {
        return [$p->id => [
            'retail' => (float) $p->selling_price,
            'area_per_unit' => (float) ($p->area_per_unit ?? 0),
        ]];
    })) !!};

    // Select2 initialization
    function initProductSelect2() {
        $('.product-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ placeholder: 'ابحث عن الصنف...', allowClear: true, dir: 'rtl', width: '100%' })
                .on('change', function() {
                    const row = this.closest('.item-row');
                    if (row) {
                        updateRowPrice(row);
                    }
                });
            }
        });
    }

    function updateRowPrice(row) {
        const productId = row.querySelector('.product-select').value;
        const product = productsData[productId];
        const priceInput = row.querySelector('.price-input');
        if (!product) { priceInput.value = 0; calculateTotals(); return; }
        priceInput.value = product.retail;
        calculateTotals();
    }

    const tileAreaEnabled = @json(feature_enabled('tile_area_tracking'));

    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discountInput = row.querySelector('.discount-input');
        const discount = discountInput ? (parseFloat(discountInput.value) || 0) : 0;
        const total = (qty * price) - discount;
        row.querySelector('.row-total').textContent = Math.max(0, total).toFixed(2);

        if (tileAreaEnabled) {
            const areaDisplay = row.querySelector('.row-area');
            if (areaDisplay) {
                const productId = row.querySelector('.product-select').value;
                const product = productsData[productId];
                areaDisplay.textContent = (product && product.area_per_unit > 0) ? (qty * product.area_per_unit).toFixed(2) : '-';
            }
        }

        return Math.max(0, total);
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => { subtotal += calculateRowTotal(row); });
        const discountType = document.getElementById('discount_type').value;
        const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
        let discount = discountType === 'percentage' ? (subtotal * discountValue / 100) : discountValue;
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalDiscount').textContent = discount.toFixed(2);
        document.getElementById('grandTotal').textContent = (subtotal - discount).toFixed(2);
    }

    function attachRowEvents(row) {
        row.querySelectorAll('.quantity-input, .price-input, .discount-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });
    }

    // Add new row
    document.getElementById('addRowBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const firstRow = document.querySelector('.item-row');
        const $firstSelect = $(firstRow).find('.product-select');
        if ($firstSelect.hasClass('select2-hidden-accessible')) { $firstSelect.select2('destroy'); }

        const newRow = firstRow.cloneNode(true);
        newRow.setAttribute('data-index', rowIndex);
        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (input.classList.contains('quantity-input')) input.value = 1;
            else if (input.classList.contains('product-select')) input.value = '';
            else input.value = 0;
        });
        newRow.querySelector('.row-total').textContent = '0.00';
        newRow.querySelector('.remove-row').style.display = 'inline-block';
        tbody.appendChild(newRow);
        rowIndex++;
        updateRemoveButtons();
        attachRowEvents(newRow);
        initProductSelect2();
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.item-row').remove();
            calculateTotals();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => { row.querySelector('.remove-row').style.display = rows.length > 1 ? 'inline-block' : 'none'; });
    }

    attachRowEvents(document.querySelector('.item-row'));
    document.getElementById('discount_type').addEventListener('change', calculateTotals);
    document.getElementById('discount_value').addEventListener('input', calculateTotals);
    initProductSelect2();
    $('#customer_id').select2({ placeholder: 'ابحث عن العميل...', allowClear: true, dir: 'rtl', width: '100%' });
});
</script>
@endsection
