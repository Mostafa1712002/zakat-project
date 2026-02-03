@extends('layouts.app')

@section('title', 'فاتورة جديدة')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ فاتورة جديدة</h1>
        <p>إنشاء فاتورة مبيعات جديدة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('invoices.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3>📋 بيانات الفاتورة</h3>

                <div class="form-group">
                    <label class="form-label">العميل *</label>
                    <select name="customer_id" class="form-control" required>
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">تاريخ الفاتورة *</label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">نوع الدفع *</label>
                        <select name="payment_type" class="form-control" required>
                            <option value="cash">نقدي</option>
                            <option value="credit">آجل</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">المستودع *</label>
                        <select name="warehouse_id" class="form-control" required>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ $warehouse->is_default ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>💰 الخصم والشحن</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">نوع الخصم</label>
                        <select name="discount_type" id="discount_type" class="form-control">
                            <option value="fixed">مبلغ ثابت</option>
                            <option value="percentage">نسبة مئوية</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">قيمة الخصم</label>
                        <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control" value="0" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">مصاريف الشحن</label>
                    <input type="number" step="0.01" name="shipping_amount" id="shipping_amount" class="form-control" value="0" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body  overflow-auto">
            <h3>🛒 الأصناف</h3>

            <table class="table text-nowrap" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width: 30%;">الصنف</th>
                        <th style="width: 12%;">نوع السعر</th>
                        <th style="width: 12%;">الكمية</th>
                        <th style="width: 14%;">سعر الوحدة</th>
                        <th style="width: 12%;">الخصم</th>
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
                        </td>
                        <td>
                            <select name="items[0][price_type]" class="form-control price-type-select">
                                <option value="retail">مستهلك</option>
                                <option value="wholesale">جملة</option>
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" class="form-control quantity-input" value="1" min="0.001" step="0.001" required></td>
                        <td><input type="number" name="items[0][unit_price]" class="form-control price-input" value="0" min="0" step="0.01" required></td>
                        <td><input type="number" name="items[0][discount_amount]" class="form-control discount-input" value="0" min="0" step="0.01"></td>
                        <td><span class="row-total">0.00</span> ج.م</td>
                        <td><button type="button" class="btn btn-sm btn-danger remove-row" style="display: none;">×</button></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7"><button type="button" class="btn btn-sm" id="addRowBtn">+ إضافة صنف</button></td>
                    </tr>
                </tfoot>
            </table>

            <div class="totals-section">
                <div class="totals-row"><span>الإجمالي الفرعي:</span> <strong id="subtotal">0.00</strong> ج.م</div>
                <div class="totals-row"><span>الخصم:</span> <strong id="totalDiscount">0.00</strong> ج.م</div>
                <div class="totals-row"><span>الشحن:</span> <strong id="totalShipping">0.00</strong> ج.م</div>
                <div class="totals-row total-final"><span>الإجمالي النهائي:</span> <strong id="grandTotal">0.00</strong> ج.م</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 حفظ الفاتورة</button>
            <a href="{{ route('invoices.index') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; }
.totals-section { margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid var(--border-color); max-width: 300px; margin-right: auto; }
.totals-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.total-final { font-size: 1.25rem; border-top: 2px solid var(--primary); padding-top: 1rem; margin-top: 0.5rem; }
.btn-lg { padding: 14px 32px; font-size: 1rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    // Products data with both prices
    const productsData = @json($products->mapWithKeys(fn($p) => [$p->id => ['retail' => $p->selling_price, 'wholesale' => $p->wholesale_price ?? $p->selling_price]]));

    document.getElementById('addRowBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const newRow = document.querySelector('.item-row').cloneNode(true);
        newRow.setAttribute('data-index', rowIndex);
        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace('[0]', '[' + rowIndex + ']');
            if (input.classList.contains('quantity-input')) input.value = 1;
            else if (input.classList.contains('product-select')) input.value = '';
            else if (input.classList.contains('price-type-select')) input.value = 'retail';
            else input.value = 0;
        });
        newRow.querySelector('.row-total').textContent = '0.00';
        newRow.querySelector('.remove-row').style.display = 'inline-block';
        tbody.appendChild(newRow);
        rowIndex++;
        updateRemoveButtons();
        attachRowEvents(newRow);
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
        rows.forEach((row, index) => {
            row.querySelector('.remove-row').style.display = rows.length > 1 ? 'inline-block' : 'none';
        });
    }

    // Get price based on type
    function getPrice(productId, priceType) {
        const product = productsData[productId];
        if (!product) return 0;
        return priceType === 'wholesale' ? product.wholesale : product.retail;
    }

    // Update price based on product and price type
    function updateRowPrice(row) {
        const productId = row.querySelector('.product-select').value;
        const priceType = row.querySelector('.price-type-select').value;
        const price = getPrice(productId, priceType);
        row.querySelector('.price-input').value = price;
        calculateTotals();
    }

    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const total = (qty * price) - discount;
        row.querySelector('.row-total').textContent = total.toFixed(2);
        return total;
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => { subtotal += calculateRowTotal(row); });
        const discountType = document.getElementById('discount_type').value;
        const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
        const shipping = parseFloat(document.getElementById('shipping_amount').value) || 0;
        let discount = discountType === 'percentage' ? (subtotal * discountValue / 100) : discountValue;
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalDiscount').textContent = discount.toFixed(2);
        document.getElementById('totalShipping').textContent = shipping.toFixed(2);
        document.getElementById('grandTotal').textContent = (subtotal - discount + shipping).toFixed(2);
    }

    function attachRowEvents(row) {
        // Product selection change
        row.querySelector('.product-select').addEventListener('change', function() {
            updateRowPrice(row);
        });
        // Price type change
        row.querySelector('.price-type-select').addEventListener('change', function() {
            updateRowPrice(row);
        });
        row.querySelectorAll('.quantity-input, .price-input, .discount-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });
    }

    attachRowEvents(document.querySelector('.item-row'));
    document.getElementById('discount_type').addEventListener('change', calculateTotals);
    document.getElementById('discount_value').addEventListener('input', calculateTotals);
    document.getElementById('shipping_amount').addEventListener('input', calculateTotals);
});
</script>
@endsection
