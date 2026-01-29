@extends('layouts.app')

@section('title', 'تعديل فاتورة البيع')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل فاتورة بيع</h1>
        <p>{{ $sale->invoice_number }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales.show', $sale) }}" class="btn">عرض</a>
        <a href="{{ route('sales.index') }}" class="btn">← رجوع للمبيعات</a>
    </div>
</div>

@php
    $items = old('items');
    if (!$items) {
        $items = $sale->items->map(function ($item) use ($products) {
            // Determine price type based on which price matches
            $product = $products->find($item->product_id);
            $priceType = 'retail';
            if ($product && $product->wholesale_price && abs($item->unit_price - $product->wholesale_price) < 0.01) {
                $priceType = 'wholesale';
            }
            return [
                'product_id' => $item->product_id,
                'price_type' => $priceType,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount ?? 0,
            ];
        })->toArray();
    }
@endphp

<form action="{{ route('sales.update', $sale) }}" method="POST" id="saleForm">
    @csrf
    @method('PUT')

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">📋 بيانات الفاتورة</h3>

                <div class="form-group">
                    <label for="customer_id" class="form-label">العميل *</label>
                    <select name="customer_id" id="customer_id" class="form-control" required>
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}>
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
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="{{ old('invoice_date', $sale->invoice_date?->format('Y-m-d')) }}" required>
                        @error('invoice_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="due_date" class="form-label">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', $sale->due_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_type" class="form-label">نوع الدفع *</label>
                        <select name="payment_type" id="payment_type" class="form-control" required>
                            <option value="cash" {{ old('payment_type', $sale->payment_type) == 'cash' ? 'selected' : '' }}>نقدي</option>
                            <option value="credit" {{ old('payment_type', $sale->payment_type) == 'credit' ? 'selected' : '' }}>آجل</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="warehouse_id" class="form-label">المستودع *</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $sale->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
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
                                <option value="{{ $branch->id }}" {{ old('branch_id', $sale->branch_id) == $branch->id ? 'selected' : '' }}>
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
                                <option value="{{ $rep->id }}" {{ old('sales_rep_id', $sale->sales_rep_id) == $rep->id ? 'selected' : '' }}>
                                    {{ $rep->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">💰 الخصم والشحن</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="discount_type" class="form-label">نوع الخصم</label>
                        <select name="discount_type" id="discount_type" class="form-control">
                            <option value="fixed" {{ old('discount_type', $sale->discount_type) == 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                            <option value="percentage" {{ old('discount_type', $sale->discount_type) == 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="discount_value" class="form-label">قيمة الخصم</label>
                        <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control" value="{{ old('discount_value', $sale->discount_value ?? 0) }}" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="shipping_amount" class="form-label">مصاريف الشحن</label>
                    <input type="number" step="0.01" name="shipping_amount" id="shipping_amount" class="form-control" value="{{ old('shipping_amount', $sale->shipping_amount ?? 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="ملاحظات على الفاتورة...">{{ old('notes', $sale->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">🛒 أصناف الفاتورة</h3>

            <div class="table-container">
                <table class="table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 25%;">الصنف</th>
                            <th style="width: 10%;">نوع السعر</th>
                            <th style="width: 10%;">المتاح</th>
                            <th style="width: 10%;">الكمية</th>
                            <th style="width: 12%;">سعر الوحدة</th>
                            <th style="width: 10%;">الخصم</th>
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
                                        <option value="{{ $product->id }}" data-track="{{ $product->track_inventory ? '1' : '0' }}" {{ ($item['product_id'] ?? null) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="items[{{ $index }}][price_type]" class="form-control price-type-select">
                                    <option value="retail" {{ ($item['price_type'] ?? 'retail') == 'retail' ? 'selected' : '' }}>مستهلك</option>
                                    <option value="wholesale" {{ ($item['price_type'] ?? '') == 'wholesale' ? 'selected' : '' }}>جملة</option>
                                </select>
                            </td>
                            <td>
                                <span class="stock-display badge badge-secondary">-</span>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" value="{{ $item['quantity'] ?? 1 }}" min="0.001" step="0.001" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][unit_price]" class="form-control price-input" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][discount_amount]" class="form-control discount-input" value="{{ $item['discount_amount'] ?? 0 }}" min="0" step="0.01">
                            </td>
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
                            <td colspan="8">
                                <button type="button" class="btn btn-sm" id="addRowBtn">+ إضافة صنف</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

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

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <button type="submit" class="btn btn-primary btn-lg">💾 تحديث الفاتورة</button>
            <a href="{{ route('sales.show', $sale) }}" class="btn btn-lg">إلغاء</a>
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

.stock-display {
    display: inline-block;
    min-width: 50px;
    text-align: center;
}

.stock-ok {
    background-color: #22c55e;
    color: white;
}

.stock-low {
    background-color: #f59e0b;
    color: white;
}

.stock-out {
    background-color: #ef4444;
    color: white;
}

.stock-loading {
    background-color: #6b7280;
    color: white;
}

.quantity-warning {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = document.querySelectorAll('.item-row').length;
    const stockUrl = "{{ route('sales.get-stock') }}";

    // Products data with both prices
    const productsData = @json($products->mapWithKeys(fn($p) => [$p->id => ['retail' => $p->selling_price, 'wholesale' => $p->wholesale_price ?? $p->selling_price, 'track' => $p->track_inventory]]));

    document.getElementById('addRowBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const newRow = document.querySelector('.item-row').cloneNode(true);

        newRow.setAttribute('data-index', rowIndex);

        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (input.classList.contains('quantity-input')) input.value = 1;
            else if (input.classList.contains('product-select')) input.value = '';
            else if (input.classList.contains('price-type-select')) input.value = 'retail';
            else input.value = 0;
        });

        newRow.querySelector('.row-total').textContent = '0.00';
        newRow.querySelector('.remove-row').style.display = 'inline-block';

        // Reset stock display
        const stockDisplay = newRow.querySelector('.stock-display');
        stockDisplay.textContent = '-';
        stockDisplay.className = 'stock-display badge badge-secondary';
        stockDisplay.removeAttribute('data-stock');

        tbody.appendChild(newRow);
        rowIndex++;
        updateRemoveButtons();
        attachRowEvents(newRow);
        calculateTotals();
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
        rows.forEach((row) => {
            const btn = row.querySelector('.remove-row');
            btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
        });
    }

    // Get price based on type
    function getPrice(productId, priceType) {
        const product = productsData[productId];
        if (!product) return 0;
        return priceType === 'wholesale' ? product.wholesale : product.retail;
    }

    // Fetch stock for a product in the selected warehouse
    async function fetchStock(row) {
        const productId = row.querySelector('.product-select').value;
        const warehouseId = document.getElementById('warehouse_id').value;
        const stockDisplay = row.querySelector('.stock-display');
        const quantityInput = row.querySelector('.quantity-input');

        if (!productId || !warehouseId) {
            stockDisplay.textContent = '-';
            stockDisplay.className = 'stock-display badge badge-secondary';
            quantityInput.classList.remove('quantity-warning');
            return;
        }

        // Check if product tracks inventory
        const product = productsData[productId];
        if (product && !product.track) {
            stockDisplay.textContent = '∞';
            stockDisplay.className = 'stock-display badge badge-secondary';
            quantityInput.classList.remove('quantity-warning');
            return;
        }

        stockDisplay.textContent = '...';
        stockDisplay.className = 'stock-display badge stock-loading';

        try {
            const response = await fetch(`${stockUrl}?product_id=${productId}&warehouse_id=${warehouseId}`);
            const data = await response.json();

            const available = data.available || 0;
            stockDisplay.textContent = available.toFixed(2);
            stockDisplay.dataset.stock = available;

            if (available <= 0) {
                stockDisplay.className = 'stock-display badge stock-out';
            } else if (available < 10) {
                stockDisplay.className = 'stock-display badge stock-low';
            } else {
                stockDisplay.className = 'stock-display badge stock-ok';
            }

            validateQuantity(row);
        } catch (error) {
            stockDisplay.textContent = '!';
            stockDisplay.className = 'stock-display badge stock-out';
        }
    }

    // Validate quantity against available stock
    function validateQuantity(row) {
        const stockDisplay = row.querySelector('.stock-display');
        const quantityInput = row.querySelector('.quantity-input');
        const productId = row.querySelector('.product-select').value;

        if (!productId) return;

        const product = productsData[productId];
        if (product && !product.track) {
            quantityInput.classList.remove('quantity-warning');
            return;
        }

        const available = parseFloat(stockDisplay.dataset.stock) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;

        if (quantity > available) {
            quantityInput.classList.add('quantity-warning');
        } else {
            quantityInput.classList.remove('quantity-warning');
        }
    }

    // Update all rows stock when warehouse changes
    function updateAllStock() {
        document.querySelectorAll('.item-row').forEach(row => {
            fetchStock(row);
        });
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

    function attachRowEvents(row) {
        // Product selection change
        row.querySelector('.product-select').addEventListener('change', function() {
            updateRowPrice(row);
            fetchStock(row);
        });

        // Price type change
        row.querySelector('.price-type-select').addEventListener('change', function() {
            updateRowPrice(row);
        });

        row.querySelectorAll('.quantity-input, .price-input, .discount-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        // Quantity change validation
        row.querySelector('.quantity-input').addEventListener('input', function() {
            validateQuantity(row);
        });
    }

    document.querySelectorAll('.item-row').forEach(row => attachRowEvents(row));
    document.getElementById('discount_type').addEventListener('change', calculateTotals);
    document.getElementById('discount_value').addEventListener('input', calculateTotals);
    document.getElementById('shipping_amount').addEventListener('input', calculateTotals);

    // Warehouse change - update all stock displays
    document.getElementById('warehouse_id').addEventListener('change', updateAllStock);

    // Form submit validation
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        let hasErrors = false;
        document.querySelectorAll('.item-row').forEach(row => {
            const productId = row.querySelector('.product-select').value;
            if (!productId) return;

            const product = productsData[productId];
            if (product && !product.track) return;

            const stockDisplay = row.querySelector('.stock-display');
            const quantityInput = row.querySelector('.quantity-input');
            const available = parseFloat(stockDisplay.dataset.stock) || 0;
            const quantity = parseFloat(quantityInput.value) || 0;

            if (quantity > available) {
                hasErrors = true;
                quantityInput.classList.add('quantity-warning');
            }
        });

        if (hasErrors) {
            e.preventDefault();
            alert('⚠️ بعض الكميات المطلوبة أكبر من المتوفر في المخزن. يرجى تعديل الكميات أو تغيير المخزن.');
        }
    });

    updateRemoveButtons();
    calculateTotals();

    // Fetch initial stock for existing items
    updateAllStock();
});
</script>
@endsection
