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
                <h3 style="margin-bottom: 16px;">💰 الخدمة</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="discount_type" class="form-label">نوع الخدمة</label>
                        <select name="discount_type" id="discount_type" class="form-control">
                            <option value="fixed" {{ old('discount_type', $sale->discount_type) == 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                            <option value="percentage" {{ old('discount_type', $sale->discount_type) == 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="discount_value" class="form-label">قيمة الخدمة</label>
                        <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control" value="{{ old('discount_value', $sale->discount_value ?? 0) }}" min="0">
                    </div>
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
                            <th style="width: 30%;">الصنف</th>
                            <th style="width: 15%;">الكمية</th>
                            <th style="width: 15%;">سعر الوحدة</th>
                            <th style="width: 10%;">أقل سعر</th>
                            <th style="width: 10%;">الخصم</th>
                            <th style="width: 12%;">الإجمالي</th>
                            <th style="width: 8%;"></th>
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
                                <input type="hidden" name="items[{{ $index }}][price_type]" value="retail">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" value="{{ $item['quantity'] ?? 1 }}" min="0.001" step="0.001" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][unit_price]" class="form-control price-input" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01" required>
                            </td>
                            <td>
                                <span class="min-price-display badge badge-secondary">-</span>
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
                    <span>الخدمة:</span>
                    <strong id="totalDiscount">0.00</strong> ج.م
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

.min-price-display {
    display: inline-block;
    min-width: 60px;
    text-align: center;
    font-size: 12px;
}

.price-warning {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
}

.price-ok {
    border-color: #22c55e !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = document.querySelectorAll('.item-row').length;

    // Products data with prices and min selling price
    const productsData = @json($products->mapWithKeys(fn($p) => [$p->id => ['retail' => $p->selling_price, 'min_price' => $p->min_selling_price ?? 0]]));

    document.getElementById('addRowBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const newRow = document.querySelector('.item-row').cloneNode(true);

        newRow.setAttribute('data-index', rowIndex);

        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (input.classList.contains('quantity-input')) input.value = 1;
            else if (input.classList.contains('product-select')) input.value = '';
            else input.value = 0;
        });

        newRow.querySelector('.row-total').textContent = '0.00';
        newRow.querySelector('.remove-row').style.display = 'inline-block';

        // Reset min price display
        const minPriceDisplay = newRow.querySelector('.min-price-display');
        minPriceDisplay.textContent = '-';
        minPriceDisplay.className = 'min-price-display badge badge-secondary';

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

    // Update price and min price display when product is selected
    function updateRowPrice(row, skipPriceUpdate = false) {
        const productId = row.querySelector('.product-select').value;
        const product = productsData[productId];
        const priceInput = row.querySelector('.price-input');
        const minPriceDisplay = row.querySelector('.min-price-display');

        if (!product) {
            if (!skipPriceUpdate) priceInput.value = 0;
            priceInput.min = 0;
            minPriceDisplay.textContent = '-';
            minPriceDisplay.className = 'min-price-display badge badge-secondary';
            return;
        }

        if (!skipPriceUpdate) priceInput.value = product.retail;
        const minPrice = product.min_price || 0;
        priceInput.min = minPrice;

        if (minPrice > 0) {
            minPriceDisplay.textContent = minPrice.toFixed(2);
            minPriceDisplay.className = 'min-price-display badge badge-warning';
        } else {
            minPriceDisplay.textContent = '-';
            minPriceDisplay.className = 'min-price-display badge badge-secondary';
        }

        validatePrice(row);
        calculateTotals();
    }

    // Validate price against minimum
    function validatePrice(row) {
        const productId = row.querySelector('.product-select').value;
        const priceInput = row.querySelector('.price-input');
        const product = productsData[productId];

        if (!product || !product.min_price) {
            priceInput.classList.remove('price-warning');
            priceInput.classList.remove('price-ok');
            return;
        }

        const currentPrice = parseFloat(priceInput.value) || 0;
        const minPrice = product.min_price;

        if (currentPrice < minPrice) {
            priceInput.classList.add('price-warning');
            priceInput.classList.remove('price-ok');
        } else {
            priceInput.classList.remove('price-warning');
            priceInput.classList.add('price-ok');
        }
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

        let discount = discountType === 'percentage' ? (subtotal * discountValue / 100) : discountValue;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalDiscount').textContent = discount.toFixed(2);
        document.getElementById('grandTotal').textContent = (subtotal - discount).toFixed(2);
    }

    function attachRowEvents(row) {
        // Product selection change
        row.querySelector('.product-select').addEventListener('change', function() {
            updateRowPrice(row);
        });

        row.querySelectorAll('.quantity-input, .price-input, .discount-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        // Price change validation
        row.querySelector('.price-input').addEventListener('input', function() {
            validatePrice(row);
        });
    }

    document.querySelectorAll('.item-row').forEach(row => {
        attachRowEvents(row);
        // Initialize min price display for existing items
        updateRowPrice(row, true);
    });
    document.getElementById('discount_type').addEventListener('change', calculateTotals);
    document.getElementById('discount_value').addEventListener('input', calculateTotals);

    // Form submit validation
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        let hasErrors = false;
        let errorMessages = [];

        document.querySelectorAll('.item-row').forEach(row => {
            const productId = row.querySelector('.product-select').value;
            if (!productId) return;

            const product = productsData[productId];
            if (!product || !product.min_price) return;

            const priceInput = row.querySelector('.price-input');
            const currentPrice = parseFloat(priceInput.value) || 0;
            const minPrice = product.min_price;

            if (currentPrice < minPrice) {
                hasErrors = true;
                priceInput.classList.add('price-warning');
                const productName = row.querySelector('.product-select option:checked').text;
                errorMessages.push(`${productName}: السعر ${currentPrice} أقل من أقل سعر بيع ${minPrice}`);
            }
        });

        if (hasErrors) {
            e.preventDefault();
            alert('⚠️ لا يمكن البيع بسعر أقل من الحد الأدنى:\n\n' + errorMessages.join('\n'));
        }
    });

    updateRemoveButtons();
    calculateTotals();
});
</script>
@endsection
