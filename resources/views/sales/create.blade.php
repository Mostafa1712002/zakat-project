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

                    @unless(auth()->user()->isSalesRep())
                    <div class="form-group">
                        <label for="due_date" class="form-label">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date') }}">
                    </div>
                    @endunless
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_type" class="form-label">نوع الدفع *</label>
                        <select name="payment_type" id="payment_type" class="form-control" required>
                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>نقدي</option>
                            <option value="credit" {{ old('payment_type') == 'credit' ? 'selected' : '' }}>آجل</option>
                        </select>
                    </div>

                    @unless(auth()->user()->isSalesRep())
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
                    @else
                    <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ $warehouses->first()?->id }}">
                    @endunless
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

                @unless(auth()->user()->isSalesRep())
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
                @else
                <input type="hidden" name="branch_id" value="{{ $currentSalesRep->branch_id }}">
                <input type="hidden" name="sales_rep_id" value="{{ $currentSalesRep->id }}">
                @endunless
            </div>
        </div>

        <!-- Service Fee -->
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
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">🛒 أصناف الفاتورة</h3>

            <div class="table-container overflow-auto">
                <table class="table text-nowrap" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 28%;">الصنف</th>
                            @if(feature_enabled('grade_system'))
                            <th style="width: 10%;">الفرز</th>
                            @endif
                            <th style="width: 8%;">المتاح</th>
                            <th style="width: 10%;">الكمية</th>
                            <th style="width: 12%;">سعر الوحدة</th>
                            @if(feature_enabled('per_item_discount'))
                            <th style="width: 10%;">الخصم</th>
                            @endif
                            @if(feature_enabled('tile_area_tracking'))
                            <th style="width: 10%;">المساحة</th>
                            @endif
                            <th style="width: 10%;">الإجمالي</th>
                            <th style="width: 5%;"></th>
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
                                <span class="stock-display badge badge-secondary">-</span>
                            </td>
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
                                $colCount = 6;
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
            <button type="submit" class="btn btn-primary btn-lg">💾 حفظ الفاتورة</button>
            <a href="{{ route('sales.index') }}" class="btn btn-lg">إلغاء</a>
        </div>
    </div>
</form>

<style>
.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr));
    gap: 1.5rem;
}

.totals-section {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid var(--border);
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
    font-size: 11px;
    padding: 4px 8px;
}

.stock-warning {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
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

.quantity-warning,
.min-price-error {
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

@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .totals-section {
        max-width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const stockCache = {}; // Cache for stock data

    // Products data with prices and min selling price
    const productsData = {!! json_encode($products->mapWithKeys(function($p) {
        return [$p->id => [
            'retail' => (float) $p->selling_price,
            'min_price' => (float) ($p->min_selling_price ?? 0),
            'track' => $p->track_inventory,
            'rep_stock' => $p->rep_stock ?? null,
            'area_per_unit' => (float) ($p->area_per_unit ?? 0),
        ]];
    })) !!};

    // Whether current user is a sales rep (stock comes from SalesRepInventory)
    const useSalesRepInventory = @json($useSalesRepInventory ?? false);

    // Get stock API URL
    const getStockUrl = '{{ route("sales.get-stock") }}';

    // Fetch stock for a product
    async function fetchStock(productId, warehouseId) {
        // For sales rep, cache by product only (warehouse not relevant)
        const cacheKey = useSalesRepInventory ? `rep_${productId}` : `${productId}_${warehouseId}`;

        if (stockCache[cacheKey] !== undefined) {
            return stockCache[cacheKey];
        }

        try {
            let url = `${getStockUrl}?product_id=${productId}`;
            if (warehouseId) {
                url += `&warehouse_id=${warehouseId}`;
            }
            const response = await fetch(url);
            const data = await response.json();
            stockCache[cacheKey] = parseFloat(data.available) || 0;
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

        if (!productId) {
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

        // For sales rep: use rep_stock directly (no warehouse needed)
        if (useSalesRepInventory && product && product.rep_stock !== null && product.rep_stock !== undefined) {
            const stock = parseFloat(product.rep_stock);
            stockDisplay.dataset.stock = stock;
            stockDisplay.textContent = Math.floor(stock);

            if (stock <= 0) {
                stockDisplay.className = 'stock-display badge stock-out';
            } else if (stock < 10) {
                stockDisplay.className = 'stock-display badge stock-low';
            } else {
                stockDisplay.className = 'stock-display badge stock-ok';
            }
            validateQuantity(row);
            return;
        }

        // For admin: fetch stock from warehouse (or all warehouses if none selected)
        stockDisplay.textContent = '...';
        stockDisplay.className = 'stock-display badge badge-secondary';

        const stock = await fetchStock(productId, warehouseId || '');
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

    // Select2 initialization
    function initProductSelect2() {
        $('.product-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ placeholder: 'ابحث عن الصنف...', allowClear: true, dir: 'rtl', width: '100%' })
                .on('change', function() {
                    const row = this.closest('.item-row');
                    if (row) {
                        updateRowPrice(row);
                        updateStockDisplay(row);
                    }
                });
            }
        });
    }

    // Add new row
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

        // Update names
        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (input.classList.contains('quantity-input')) input.value = 1;
            else if (input.classList.contains('product-select')) input.value = '';
            else input.value = 0;
        });

        newRow.querySelector('.row-total').textContent = '0.00';
        newRow.querySelector('.remove-row').style.display = 'inline-block';

        // Reset stock display
        const stockDisplay = newRow.querySelector('.stock-display');
        stockDisplay.textContent = '-';
        stockDisplay.className = 'stock-display badge badge-secondary';
        stockDisplay.dataset.stock = '0';

        // Reset warnings
        newRow.querySelector('.quantity-input').classList.remove('quantity-warning');

        tbody.appendChild(newRow);
        rowIndex++;
        updateRemoveButtons();
        attachRowEvents(newRow);
        initProductSelect2();
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

    // Update price when product is selected
    function updateRowPrice(row) {
        const productId = row.querySelector('.product-select').value;
        const product = productsData[productId];
        const priceInput = row.querySelector('.price-input');

        if (!product) {
            priceInput.value = 0;
            removeMinPriceHint(row);
            return;
        }

        priceInput.value = product.retail;
        showMinPriceHint(row, product.min_price);
        calculateTotals();
    }

    // Show min price hint below price input
    function showMinPriceHint(row, minPrice) {
        removeMinPriceHint(row);
        if (minPrice > 0) {
            const priceInput = row.querySelector('.price-input');
            const hint = document.createElement('small');
            hint.className = 'min-price-hint';
            hint.style.cssText = 'color: #64748b; font-size: 11px; display: block; margin-top: 2px;';
            hint.textContent = 'أقل سعر: ' + parseFloat(minPrice).toFixed(2) + ' ج.م';
            priceInput.parentNode.appendChild(hint);
        }
    }

    function removeMinPriceHint(row) {
        const hint = row.querySelector('.min-price-hint');
        if (hint) hint.remove();
    }

    // Validate price against min_selling_price
    function validateMinPrice(row) {
        const productId = row.querySelector('.product-select').value;
        const product = productsData[productId];
        const priceInput = row.querySelector('.price-input');
        const price = parseFloat(priceInput.value) || 0;

        if (!product || !product.min_price || product.min_price <= 0) {
            priceInput.classList.remove('min-price-error');
            return true;
        }

        if (price < product.min_price) {
            priceInput.classList.add('min-price-error');
            return false;
        } else {
            priceInput.classList.remove('min-price-error');
            return true;
        }
    }

    // Calculate row total
    const perItemDiscountEnabled = @json(feature_enabled('per_item_discount'));

    const tileAreaEnabled = @json(feature_enabled('tile_area_tracking'));

    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discountInput = row.querySelector('.discount-input');
        const discount = discountInput ? (parseFloat(discountInput.value) || 0) : 0;
        const total = (qty * price) - discount;
        row.querySelector('.row-total').textContent = Math.max(0, total).toFixed(2);

        // Update area display
        if (tileAreaEnabled) {
            const areaDisplay = row.querySelector('.row-area');
            if (areaDisplay) {
                const productId = row.querySelector('.product-select').value;
                const product = productsData[productId];
                if (product && product.area_per_unit > 0) {
                    areaDisplay.textContent = (qty * product.area_per_unit).toFixed(2);
                } else {
                    areaDisplay.textContent = '-';
                }
            }
        }

        return Math.max(0, total);
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
        // Product selection change is handled by Select2 in initProductSelect2()

        row.querySelectorAll('.quantity-input, .price-input, .discount-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        // Quantity change validation
        row.querySelector('.quantity-input').addEventListener('input', function() {
            validateQuantity(row);
        });

        // Price change validation against min price
        row.querySelector('.price-input').addEventListener('input', function() {
            validateMinPrice(row);
        });
    }

    // Warehouse change - fetch products for this warehouse (admin only)
    document.getElementById('warehouse_id').addEventListener('change', function() {
        // Clear cache when warehouse changes
        Object.keys(stockCache).forEach(key => delete stockCache[key]);

        if (!useSalesRepInventory && this.value) {
            // Admin: fetch products from selected warehouse
            fetch(`/warehouses/${this.value}/products-with-stock`)
                .then(r => r.json())
                .then(products => {
                    // Rebuild productsData
                    Object.keys(productsData).forEach(key => delete productsData[key]);
                    products.forEach(p => {
                        productsData[p.id] = {
                            retail: parseFloat(p.selling_price) || 0,
                            min_price: parseFloat(p.min_selling_price) || 0,
                            track: p.track_inventory,
                            rep_stock: null
                        };
                        // Pre-fill stock cache
                        stockCache[`${p.id}_${document.getElementById('warehouse_id').value}`] = parseFloat(p.available) || 0;
                    });

                    // Destroy existing Select2
                    $('.product-select').each(function() {
                        if ($(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2('destroy');
                        }
                    });

                    // Rebuild all product selects with fetched products
                    document.querySelectorAll('.product-select').forEach(select => {
                        select.innerHTML = '<option value="">اختر الصنف</option>';
                        products.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = p.name;
                            opt.dataset.track = p.track_inventory ? '1' : '0';
                            select.appendChild(opt);
                        });
                    });

                    // Re-init Select2
                    initProductSelect2();

                    // Update stock displays
                    document.querySelectorAll('.item-row').forEach(row => {
                        updateStockDisplay(row);
                    });
                });
        } else {
            // Sales rep or no warehouse selected: just update stock displays
            document.querySelectorAll('.item-row').forEach(row => {
                updateStockDisplay(row);
            });
        }
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

    // Init Select2 for product and customer selects
    initProductSelect2();
    $('#customer_id').select2({ placeholder: 'ابحث عن العميل...', allowClear: true, dir: 'rtl', width: '100%' });

    // For admin: trigger warehouse change on load to filter products by default warehouse
    if (!useSalesRepInventory) {
        const warehouseEl = document.getElementById('warehouse_id');
        if (warehouseEl && warehouseEl.value) {
            warehouseEl.dispatchEvent(new Event('change'));
        }
    }

    // Form submit validation
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        let hasErrors = false;
        let errorMessages = [];

        document.querySelectorAll('.item-row').forEach(row => {
            const productId = row.querySelector('.product-select').value;
            if (!productId) return;

            const product = productsData[productId];
            const quantityInput = row.querySelector('.quantity-input');
            const stockDisplay = row.querySelector('.stock-display');
            const quantity = parseFloat(quantityInput.value) || 0;
            const stock = parseFloat(stockDisplay.dataset.stock) || 0;
            const productName = row.querySelector('.product-select option:checked').text;

            // Check stock availability (only for tracked products)
            if (product && product.track && quantity > stock) {
                hasErrors = true;
                quantityInput.classList.add('quantity-warning');
                errorMessages.push(`${productName}: الكمية المطلوبة (${quantity}) أكبر من المتاح في المخزون (${stock})`);
            }

            // Check min price
            const priceInput = row.querySelector('.price-input');
            const price = parseFloat(priceInput.value) || 0;
            if (product && product.min_price > 0 && price < product.min_price) {
                hasErrors = true;
                priceInput.classList.add('min-price-error');
                errorMessages.push(`${productName}: السعر (${price}) أقل من الحد الأدنى (${product.min_price})`);
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
