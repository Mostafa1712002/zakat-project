@extends('layouts.app')

@section('title', 'فاتورة مبيعات جديدة')

@section('content')
<div class="page-header">
    <div>
        <h1>➕ فاتورة مبيعات جديدة</h1>
        <p>إنشاء فاتورة بيع جديدة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales.index') }}" class="btn">← رجوع للمبيعات</a>
    </div>
</div>

<form action="{{ route('sales.store') }}" method="POST" id="saleForm">
    @csrf

    <div class="grid-2">
        <!-- Customer & Invoice Info -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">📋 بيانات الفاتورة</h3>

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
                        <label for="invoice_date" class="form-label">تاريخ الفاتورة *</label>
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        @error('invoice_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="due_date" class="form-label">تاريخ الاستحقاق</label>
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
            </div>
        </div>

        <!-- Discount & Shipping -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">💰 الخصم والشحن</h3>

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

                <div class="form-group">
                    <label for="shipping_amount" class="form-label">مصاريف الشحن</label>
                    <input type="number" step="0.01" name="shipping_amount" id="shipping_amount" class="form-control" value="{{ old('shipping_amount', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="ملاحظات على الفاتورة...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">🛒 أصناف الفاتورة</h3>

            <div class="table-container">
                <table class="table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;">الصنف</th>
                            <th style="width: 15%;">الكمية</th>
                            <th style="width: 15%;">سعر الوحدة</th>
                            <th style="width: 15%;">الخصم</th>
                            <th style="width: 15%;">الإجمالي</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row" data-index="0">
                            <td>
                                <select name="items[0][product_id]" class="form-control product-select" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">
                                            {{ $product->name }} - {{ number_format($product->selling_price, 2) }} ج.م
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control quantity-input" value="1" min="0.001" step="0.001" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][unit_price]" class="form-control price-input" value="0" min="0" step="0.01" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][discount_amount]" class="form-control discount-input" value="0" min="0" step="0.01">
                            </td>
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
                            <td colspan="6">
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
                <div class="totals-row">
                    <span>الشحن:</span>
                    <strong id="totalShipping">0.00</strong> ج.م
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
            <button type="submit" class="btn btn-primary btn-lg">💾 حفظ الفاتورة</button>
            <a href="{{ route('sales.index') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.totals-section {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid var(--border-color);
    max-width: 300px;
    margin-right: auto;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.total-final {
    font-size: 1.25rem;
    border-top: 2px solid var(--primary);
    padding-top: 1rem;
    margin-top: 0.5rem;
}

.btn-lg {
    padding: 14px 32px;
    font-size: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const productsData = @json($products->mapWithKeys(fn($p) => [$p->id => $p->selling_price]));

    // Add new row
    document.getElementById('addRowBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const newRow = document.querySelector('.item-row').cloneNode(true);

        newRow.setAttribute('data-index', rowIndex);

        // Update names
        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace('[0]', '[' + rowIndex + ']');
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
    });

    // Remove row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.item-row').remove();
            calculateTotals();
            updateRemoveButtons();
        }
    });

    // Update remove buttons visibility
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.remove-row');
            btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
        });
    }

    // Calculate row total
    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const total = (qty * price) - discount;
        row.querySelector('.row-total').textContent = total.toFixed(2);
        return total;
    }

    // Calculate all totals
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            subtotal += calculateRowTotal(row);
        });

        const discountType = document.getElementById('discount_type').value;
        const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
        const shipping = parseFloat(document.getElementById('shipping_amount').value) || 0;

        let discount = discountType === 'percentage' ? (subtotal * discountValue / 100) : discountValue;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalDiscount').textContent = discount.toFixed(2);
        document.getElementById('totalShipping').textContent = shipping.toFixed(2);
        document.getElementById('grandTotal').textContent = (subtotal - discount + shipping).toFixed(2);
    }

    // Attach events to row
    function attachRowEvents(row) {
        row.querySelector('.product-select').addEventListener('change', function() {
            const price = productsData[this.value] || 0;
            row.querySelector('.price-input').value = price;
            calculateTotals();
        });

        row.querySelectorAll('.quantity-input, .price-input, .discount-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });
    }

    // Initial setup
    attachRowEvents(document.querySelector('.item-row'));
    document.getElementById('discount_type').addEventListener('change', calculateTotals);
    document.getElementById('discount_value').addEventListener('input', calculateTotals);
    document.getElementById('shipping_amount').addEventListener('input', calculateTotals);
});
</script>
@endsection
