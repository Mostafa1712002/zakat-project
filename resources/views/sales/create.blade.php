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

                <!-- Advance Payment Section - Only for Credit Sales -->
                <div id="advancePaymentSection" class="advance-payment-section" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="advance_payment" class="form-label">💵 دفعة مقدمة</label>
                            <input type="number" step="0.01" name="advance_payment" id="advance_payment" class="form-control" value="{{ old('advance_payment', 0) }}" min="0">
                            <small class="text-muted">المبلغ المدفوع الآن (اتركه 0 إذا لم يدفع شيء)</small>
                        </div>
                        <div class="form-group">
                            <label for="advance_payment_method" class="form-label">طريقة الدفع</label>
                            <select name="advance_payment_method" id="advance_payment_method" class="form-control">
                                <option value="cash">نقدي</option>
                                <option value="bank_transfer">تحويل بنكي</option>
                                <option value="instapay">انستا باي</option>
                                <option value="vodafone_cash">فودافون كاش</option>
                                <option value="card">بطاقة</option>
                            </select>
                        </div>
                    </div>
                    <div class="advance-payment-summary" id="advancePaymentSummary" style="display: none;">
                        <div class="summary-item">
                            <span>إجمالي الفاتورة:</span>
                            <strong id="summaryTotal">0.00</strong> ج.م
                        </div>
                        <div class="summary-item">
                            <span>الدفعة المقدمة:</span>
                            <strong id="summaryAdvance" class="text-success">0.00</strong> ج.م
                        </div>
                        <div class="summary-item summary-remaining">
                            <span>المتبقي (آجل):</span>
                            <strong id="summaryRemaining" class="text-danger">0.00</strong> ج.م
                        </div>
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

        <!-- Service Fee -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 16px;">💰 الخدمة</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="discount_type" class="form-label">نوع الخدمة</label>
                        <select name="discount_type" id="discount_type" class="form-control">
                            <option value="fixed">مبلغ ثابت</option>
                            <option value="percentage">نسبة مئوية</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="discount_value" class="form-label">قيمة الخدمة</label>
                        <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control" value="{{ old('discount_value', 0) }}" min="0">
                    </div>
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
                            <th style="width: 28%;">الصنف</th>
                            <th style="width: 12%;">المتاح</th>
                            <th style="width: 12%;">الكمية</th>
                            <th style="width: 15%;">سعر الوحدة</th>
                            <th style="width: 13%;">أقل سعر</th>
                            <th style="width: 12%;">الإجمالي</th>
                            <th style="width: 8%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row" data-index="0">
                            <td>
                                <select name="items[0][product_id]" class="form-control product-select" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-track="{{ $product->track_inventory ? '1' : '0' }}">
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="items[0][price_type]" value="retail">
                                <input type="hidden" name="items[0][discount_amount]" class="discount-input" value="0">
                            </td>
                            <td>
                                <span class="stock-display badge badge-secondary">-</span>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control quantity-input" value="1" min="0.001" step="0.001" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][unit_price]" class="form-control price-input" value="0" min="0" step="0.01" required>
                            </td>
                            <td>
                                <span class="min-price-display badge badge-secondary">-</span>
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
                            <td colspan="7">
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

.min-price-display,
.stock-display {
    display: inline-block;
    min-width: 50px;
    text-align: center;
    font-size: 11px;
    padding: 4px 8px;
}

.price-warning,
.stock-warning {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
}

.price-ok {
    border-color: #22c55e !important;
}

.stock-ok {
    background: #dcfce7 !important;
    color: #166534 !important;
}

.stock-low {
    background: #fef3c7 !important;
    color: #92400e !important;
}

.stock-out {
    background: #fee2e2 !important;
    color: #991b1b !important;
}

.quantity-warning {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
    animation: pulse-warning 1s infinite;
}

@keyframes pulse-warning {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    50% { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0); }
}

.advance-payment-section {
    margin-top: 16px;
    padding: 16px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #86efac;
    border-radius: 8px;
}

.advance-payment-summary {
    margin-top: 12px;
    padding: 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
}

.summary-remaining {
    border-top: 2px solid var(--primary);
    padding-top: 10px;
    margin-top: 6px;
    font-size: 1.1em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const stockCache = {}; // Cache for stock data

    // Products data with prices and min selling price
    const productsData = @json($products->mapWithKeys(fn($p) => [$p->id => ['retail' => $p->selling_price, 'min_price' => $p->min_selling_price ?? 0, 'track' => $p->track_inventory]]));

    // Get stock API URL
    const getStockUrl = '{{ route("sales.get-stock") }}';

    // Fetch stock for a product
    async function fetchStock(productId, warehouseId) {
        const cacheKey = `${productId}_${warehouseId}`;

        if (stockCache[cacheKey] !== undefined) {
            return stockCache[cacheKey];
        }

        try {
            const response = await fetch(`${getStockUrl}?product_id=${productId}&warehouse_id=${warehouseId}`);
            const data = await response.json();
            stockCache[cacheKey] = data.available || 0;
            return stockCache[cacheKey];
        } catch (error) {
            console.error('Error fetching stock:', error);
            return 0;
        }
    }

    // Update stock display for a row
    async function updateStockDisplay(row) {
        const productId = row.querySelector('.product-select').value;
        const warehouseId = document.getElementById('warehouse_id').value;
        const stockDisplay = row.querySelector('.stock-display');
        const product = productsData[productId];

        if (!productId || !warehouseId) {
            stockDisplay.textContent = '-';
            stockDisplay.className = 'stock-display badge badge-secondary';
            stockDisplay.dataset.stock = '0';
            return;
        }

        // If product doesn't track inventory, show unlimited
        if (product && !product.track) {
            stockDisplay.textContent = '∞';
            stockDisplay.className = 'stock-display badge badge-success';
            stockDisplay.dataset.stock = '999999';
            return;
        }

        stockDisplay.textContent = '...';
        stockDisplay.className = 'stock-display badge badge-secondary';

        const stock = await fetchStock(productId, warehouseId);
        stockDisplay.dataset.stock = stock;
        stockDisplay.textContent = stock.toFixed(0);

        if (stock <= 0) {
            stockDisplay.className = 'stock-display badge stock-out';
        } else if (stock < 10) {
            stockDisplay.className = 'stock-display badge stock-low';
        } else {
            stockDisplay.className = 'stock-display badge stock-ok';
        }

        validateQuantity(row);
    }

    // Validate quantity against stock
    function validateQuantity(row) {
        const quantityInput = row.querySelector('.quantity-input');
        const stockDisplay = row.querySelector('.stock-display');
        const quantity = parseFloat(quantityInput.value) || 0;
        const stock = parseFloat(stockDisplay.dataset.stock) || 0;
        const productId = row.querySelector('.product-select').value;
        const product = productsData[productId];

        // Skip validation if product doesn't track inventory
        if (product && !product.track) {
            quantityInput.classList.remove('quantity-warning');
            return true;
        }

        if (productId && quantity > stock) {
            quantityInput.classList.add('quantity-warning');
            return false;
        } else {
            quantityInput.classList.remove('quantity-warning');
            return true;
        }
    }

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

        // Reset displays
        const minPriceDisplay = newRow.querySelector('.min-price-display');
        minPriceDisplay.textContent = '-';
        minPriceDisplay.className = 'min-price-display badge badge-secondary';

        const stockDisplay = newRow.querySelector('.stock-display');
        stockDisplay.textContent = '-';
        stockDisplay.className = 'stock-display badge badge-secondary';
        stockDisplay.dataset.stock = '0';

        // Reset warnings
        newRow.querySelector('.quantity-input').classList.remove('quantity-warning');
        newRow.querySelector('.price-input').classList.remove('price-warning', 'price-ok');

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

    // Update price and min price display when product is selected
    function updateRowPrice(row) {
        const productId = row.querySelector('.product-select').value;
        const product = productsData[productId];
        const priceInput = row.querySelector('.price-input');
        const minPriceDisplay = row.querySelector('.min-price-display');

        if (!product) {
            priceInput.value = 0;
            priceInput.min = 0;
            minPriceDisplay.textContent = '-';
            minPriceDisplay.className = 'min-price-display badge badge-secondary';
            return;
        }

        priceInput.value = product.retail;
        const minPrice = product.min_price || 0;
        priceInput.min = minPrice;

        // Always show the min price value
        minPriceDisplay.textContent = minPrice.toFixed(2);
        if (minPrice > 0) {
            minPriceDisplay.className = 'min-price-display badge badge-warning';
        } else {
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

    // Calculate row total
    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const total = qty * price;
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

        let discount = discountType === 'percentage' ? (subtotal * discountValue / 100) : discountValue;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalDiscount').textContent = discount.toFixed(2);
        document.getElementById('grandTotal').textContent = (subtotal - discount).toFixed(2);
    }

    // Attach events to row
    function attachRowEvents(row) {
        // Product selection change
        row.querySelector('.product-select').addEventListener('change', function() {
            updateRowPrice(row);
            updateStockDisplay(row);
        });

        row.querySelectorAll('.quantity-input, .price-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        // Quantity change validation
        row.querySelector('.quantity-input').addEventListener('input', function() {
            validateQuantity(row);
        });

        // Price change validation
        row.querySelector('.price-input').addEventListener('input', function() {
            validatePrice(row);
        });
    }

    // Warehouse change - update all stock displays
    document.getElementById('warehouse_id').addEventListener('change', function() {
        // Clear cache when warehouse changes
        Object.keys(stockCache).forEach(key => delete stockCache[key]);

        document.querySelectorAll('.item-row').forEach(row => {
            updateStockDisplay(row);
        });
    });

    // Toggle advance payment section based on payment type
    const paymentTypeSelect = document.getElementById('payment_type');
    const advancePaymentSection = document.getElementById('advancePaymentSection');
    const advancePaymentInput = document.getElementById('advance_payment');
    const advancePaymentSummary = document.getElementById('advancePaymentSummary');

    function toggleAdvancePayment() {
        if (paymentTypeSelect.value === 'credit') {
            advancePaymentSection.style.display = 'block';
        } else {
            advancePaymentSection.style.display = 'none';
            advancePaymentInput.value = 0;
        }
        updateAdvancePaymentSummary();
    }

    function updateAdvancePaymentSummary() {
        const total = parseFloat(document.getElementById('grandTotal').textContent) || 0;
        const advance = parseFloat(advancePaymentInput.value) || 0;
        const remaining = Math.max(0, total - advance);

        document.getElementById('summaryTotal').textContent = total.toFixed(2);
        document.getElementById('summaryAdvance').textContent = advance.toFixed(2);
        document.getElementById('summaryRemaining').textContent = remaining.toFixed(2);

        if (advance > 0 && paymentTypeSelect.value === 'credit') {
            advancePaymentSummary.style.display = 'block';
        } else {
            advancePaymentSummary.style.display = 'none';
        }

        // Validate advance payment doesn't exceed total
        if (advance > total && total > 0) {
            advancePaymentInput.classList.add('price-warning');
        } else {
            advancePaymentInput.classList.remove('price-warning');
        }
    }

    paymentTypeSelect.addEventListener('change', toggleAdvancePayment);
    advancePaymentInput.addEventListener('input', updateAdvancePaymentSummary);

    // Update summary when totals change
    const originalCalculateTotals = calculateTotals;
    calculateTotals = function() {
        originalCalculateTotals();
        updateAdvancePaymentSummary();
    };

    // Initial setup
    attachRowEvents(document.querySelector('.item-row'));
    document.getElementById('discount_type').addEventListener('change', calculateTotals);
    document.getElementById('discount_value').addEventListener('input', calculateTotals);
    toggleAdvancePayment();

    // Form submit validation
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        let hasErrors = false;
        let errorMessages = [];

        document.querySelectorAll('.item-row').forEach(row => {
            const productId = row.querySelector('.product-select').value;
            if (!productId) return;

            const product = productsData[productId];
            const priceInput = row.querySelector('.price-input');
            const quantityInput = row.querySelector('.quantity-input');
            const stockDisplay = row.querySelector('.stock-display');
            const currentPrice = parseFloat(priceInput.value) || 0;
            const quantity = parseFloat(quantityInput.value) || 0;
            const stock = parseFloat(stockDisplay.dataset.stock) || 0;
            const productName = row.querySelector('.product-select option:checked').text;

            // Check minimum price
            if (product && product.min_price && currentPrice < product.min_price) {
                hasErrors = true;
                priceInput.classList.add('price-warning');
                errorMessages.push(`${productName}: السعر ${currentPrice} أقل من أقل سعر بيع ${product.min_price}`);
            }

            // Check stock availability (only for tracked products)
            if (product && product.track && quantity > stock) {
                hasErrors = true;
                quantityInput.classList.add('quantity-warning');
                errorMessages.push(`${productName}: الكمية المطلوبة (${quantity}) أكبر من المتاح في المخزون (${stock})`);
            }
        });

        if (hasErrors) {
            e.preventDefault();
            alert('⚠️ لا يمكن إنشاء الفاتورة:\n\n' + errorMessages.join('\n'));
        }
    });
});
</script>
@endsection
