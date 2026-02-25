@extends('layouts.app')

@section('title', 'تعديل فاتورة الشراء')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل فاتورة الشراء</h1>
        <p>{{ $purchase->invoice_number }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('purchases.show', $purchase) }}" class="btn">عرض</a>
        <a href="{{ route('purchases.index') }}" class="btn">← رجوع للمشتريات</a>
    </div>
</div>

@php
    $items = old('items');
    if (!$items) {
        $items = $purchase->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_cost,
                'discount_amount' => $item->discount_amount ?? 0,
            ];
        })->toArray();
    }
@endphp

<form action="{{ route('purchases.update', $purchase) }}" method="POST" id="purchaseForm">
    @csrf
    @method('PUT')

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">📋 بيانات الفاتورة</h3>

                <div class="form-group">
                    <label class="form-label">المورد *</label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">تاريخ التوريد *</label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', $purchase->invoice_date?->format('Y-m-d')) }}" required>
                        @error('invoice_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $purchase->due_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">نوع الدفع *</label>
                        <select name="payment_type" id="payment_type" class="form-control" required>
                            <option value="cash" {{ old('payment_type', $purchase->payment_type) == 'cash' ? 'selected' : '' }}>نقدي</option>
                            <option value="credit" {{ old('payment_type', $purchase->payment_type) == 'credit' ? 'selected' : '' }}>آجل</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">المخزن *</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $purchase->warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">حالة الفاتورة *</label>
                    <select name="status" class="form-control" required>
                        <option value="draft" {{ old('status', $purchase->status) == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="ordered" {{ old('status', $purchase->status) == 'ordered' ? 'selected' : '' }}>تم الطلب</option>
                        <option value="received" {{ old('status', $purchase->status) == 'received' ? 'selected' : '' }}>مستلم</option>
                    </select>
                </div>

                {{-- حقول الدفعة المقدمة (تظهر فقط عند اختيار آجل) --}}
                <div id="advance-payment-section" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">دفعة مقدمة</label>
                            <input type="number" name="advance_payment" id="advance_payment" class="form-control" value="0" min="0" step="0.01" placeholder="0.00">
                            <small class="text-muted">مبلغ يُدفع مقدماً من إجمالي الفاتورة</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">طريقة الدفع</label>
                            <select name="advance_payment_method" id="advance_payment_method" class="form-control">
                                <option value="cash">نقدي</option>
                                <option value="bank_transfer">تحويل بنكي</option>
                                <option value="instapay">انستاباي</option>
                                <option value="vodafone_cash">فودافون كاش</option>
                                <option value="card">بطاقة</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الخصم العام -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">💰 الخصم</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">نوع الخصم</label>
                        <select name="discount_type" id="discount_type" class="form-control">
                            <option value="fixed" {{ old('discount_type', $purchase->discount_type) == 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                            <option value="percentage" {{ old('discount_type', $purchase->discount_type) == 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">قيمة الخصم</label>
                        <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control" value="{{ old('discount_value', $purchase->discount_value ?? 0) }}" min="0">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">🛒 أصناف الفاتورة</h3>

            <div class="table-container overflow-auto">
                <table class="table text-nowrap" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: {{ feature_enabled('per_item_discount') ? '28%' : '35%' }};">الصنف</th>
                            <th style="width: 12%;">الكمية</th>
                            <th style="width: 12%;">سعر الشراء</th>
                            @if(feature_enabled('per_item_discount'))
                            <th style="width: 10%;">الخصم %</th>
                            @endif
                            <th style="width: 12%;">الإجمالي</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @foreach($items as $index => $item)
                        <tr class="item-row" data-index="{{ $index }}">
                            <td>
                                <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ ($item['product_id'] ?? null) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @unless(feature_enabled('per_item_discount'))
                                <input type="hidden" name="items[{{ $index }}][discount_amount]" class="discount-input" value="{{ $item['discount_amount'] ?? 0 }}">
                                @endunless
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" value="{{ $item['quantity'] ?? 1 }}" min="0.001" step="0.001" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][unit_price]" class="form-control price-input" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01" required>
                            </td>
                            @if(feature_enabled('per_item_discount'))
                            <td>
                                <input type="number" name="items[{{ $index }}][discount_amount]" class="form-control discount-input" value="{{ $item['discount_amount'] ?? 0 }}" min="0" max="100" step="0.01">
                            </td>
                            @endif
                            <td>
                                <span class="row-total">0.00</span> ج.م
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-row" style="display: none;">×</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="{{ feature_enabled('per_item_discount') ? 6 : 5 }}">
                                <button type="button" class="btn btn-sm" id="addRowBtn">+ إضافة صنف</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="totals-section">
                <div class="totals-row"><span>الإجمالي الفرعي:</span> <strong id="subtotal">0.00</strong> ج.م</div>
                <div class="totals-row"><span>الخصم:</span> <strong id="totalDiscount">0.00</strong> ج.م</div>
                <div class="totals-row total-final"><span>الإجمالي النهائي:</span> <strong id="grandTotal">0.00</strong> ج.م</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $purchase->notes) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">💾 تحديث الفاتورة</button>
            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; }
.totals-section { margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid var(--border-color); max-width: 300px; margin-right: auto; }
.totals-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.total-final { font-size: 1.25rem; border-top: 2px solid var(--primary); padding-top: 1rem; margin-top: 0.5rem; }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
@media (max-width: 768px) {
    .grid-2 { grid-template-columns: 1fr; gap: 1rem; }
    .form-row { grid-template-columns: 1fr; }
    .totals-section { max-width: 100%; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = {{ count($items) }};
    let supplierProducts = [];
    let productsLoaded = false;
    const skipPriceUpdateOnLoad = true;

    // All products (for pre-populated items)
    const allProducts = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'cost_price' => $p->cost_price]));

    // Toggle advance payment section
    const paymentTypeSelect = document.getElementById('payment_type');
    const advanceSection = document.getElementById('advance-payment-section');
    function toggleAdvancePayment() {
        advanceSection.style.display = paymentTypeSelect.value === 'credit' ? 'block' : 'none';
        if (paymentTypeSelect.value !== 'credit') {
            document.getElementById('advance_payment').value = 0;
        }
    }
    paymentTypeSelect.addEventListener('change', toggleAdvancePayment);
    toggleAdvancePayment();

    // Init Select2 for supplier
    $('#supplier_id').select2({ placeholder: 'ابحث عن المورد...', allowClear: true, dir: 'rtl', width: '100%' });

    // Supplier change handler
    $('#supplier_id').on('change', function() {
        if (this.value) {
            fetchSupplierProducts(this.value);
        } else {
            supplierProducts = [];
            productsLoaded = false;
            updateAllProductSelects();
        }
    });

    function fetchSupplierProducts(supplierId) {
        fetch(`/suppliers/${supplierId}/products`)
            .then(r => r.json())
            .then(products => {
                supplierProducts = products;
                productsLoaded = true;
                updateAllProductSelects();
            });
    }

    function initPurchaseSelect2() {
        $('.product-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ placeholder: 'ابحث عن الصنف...', allowClear: true, dir: 'rtl', width: '100%' })
                .on('change', function() {
                    const row = this.closest('.item-row');
                    if (row) {
                        const product = supplierProducts.find(p => p.id == this.value);
                        if (product) {
                            row.querySelector('.price-input').value = product.cost_price;
                        }
                        calculateTotals();
                    }
                });
            }
        });
    }

    function updateAllProductSelects() {
        const productsToUse = productsLoaded ? supplierProducts : allProducts;

        $('.product-select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });

        document.querySelectorAll('.product-select').forEach(select => {
            const currentVal = select.value;
            select.innerHTML = '<option value="">اختر الصنف</option>';

            productsToUse.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name + (p.cost_price ? ' - ' + parseFloat(p.cost_price).toFixed(2) + ' ج.م' : '');
                if (p.id == currentVal) opt.selected = true;
                select.appendChild(opt);
            });
        });

        initPurchaseSelect2();
    }

    // Load supplier products on page load if supplier is selected
    const initialSupplierId = document.getElementById('supplier_id').value;
    if (initialSupplierId) {
        fetchSupplierProducts(initialSupplierId);
    } else {
        // Still init select2 for existing items using allProducts
        initPurchaseSelect2();
    }

    document.getElementById('addRowBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const firstRow = document.querySelector('.item-row');

        // Destroy Select2 before cloning
        const $firstSelect = $(firstRow).find('.product-select');
        if ($firstSelect.hasClass('select2-hidden-accessible')) {
            $firstSelect.select2('destroy');
        }

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
        initPurchaseSelect2();
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
        rows.forEach(row => {
            row.querySelector('.remove-row').style.display = rows.length > 1 ? 'inline-block' : 'none';
        });
    }

    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discountInput = row.querySelector('.discount-input');
        let discountPercent = discountInput ? (parseFloat(discountInput.value) || 0) : 0;
        if (discountPercent > 100) { discountPercent = 100; discountInput.value = 100; }
        if (discountPercent < 0) { discountPercent = 0; discountInput.value = 0; }
        const lineTotal = qty * price;
        const total = lineTotal - (lineTotal * discountPercent / 100);
        row.querySelector('.row-total').textContent = Math.max(0, total).toFixed(2);
        return Math.max(0, total);
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            subtotal += calculateRowTotal(row);
        });

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

    // Attach events to existing rows
    document.querySelectorAll('.item-row').forEach(row => attachRowEvents(row));

    document.getElementById('discount_type').addEventListener('change', calculateTotals);
    document.getElementById('discount_value').addEventListener('input', calculateTotals);

    updateRemoveButtons();
    calculateTotals();
});
</script>
@endsection
