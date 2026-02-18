@extends('layouts.app')

@section('title', 'مخزون ' . $salesRep->name)

@section('content')
<div class="page-header">
    <div>
        <h1>📦 مخزون {{ $salesRep->name }}</h1>
        <p>تخصيص وإدارة الأصناف</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.sales-rep-inventory.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <!-- نموذج تخصيص أصناف -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">➕ تخصيص أصناف للمندوب</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sales-rep-inventory.allocate', $salesRep) }}" method="POST" id="allocateForm">
                @csrf

                <div class="form-group">
                    <label for="warehouse_id" class="form-label">من مخزن *</label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                        <option value="">-- اختر المخزن --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <h4 style="margin: 16px 0 8px;">📦 الأصناف</h4>
                <div class="table-container overflow-auto">
                    <table class="table text-nowrap" id="allocateItemsTable">
                        <thead>
                            <tr>
                                <th style="width: 45%;">الصنف</th>
                                <th style="width: 15%;">المتاح</th>
                                <th style="width: 25%;">الكمية</th>
                                <th style="width: 15%;"></th>
                            </tr>
                        </thead>
                        <tbody id="allocateItemsBody">
                            <tr class="allocate-row" data-index="0">
                                <td>
                                    <select name="items[0][product_id]" class="form-control alloc-product-select" required>
                                        <option value="">اختر الصنف</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="alloc-stock-badge badge badge-secondary">-</span>
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]" class="form-control alloc-quantity-input" value="1" min="0.001" step="0.001" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger alloc-remove-row" style="display: none;">×</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">
                                    <button type="button" class="btn btn-sm" id="allocAddRowBtn" disabled>+ إضافة صنف</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="form-group" style="margin-top: 12px;">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    📦 تخصيص للمندوب
                </button>
            </form>
        </div>
    </div>

    <!-- نموذج استرجاع صنف -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">↩️ استرجاع صنف من المندوب</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sales-rep-inventory.return', $salesRep) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="return_product_id" class="form-label">الصنف *</label>
                    <select name="product_id" id="return_product_id" class="form-control" required>
                        <option value="">-- اختر صنف --</option>
                        @foreach($inventory as $item)
                            @if($item->available_quantity > 0)
                            <option value="{{ $item->product_id }}" data-max="{{ $item->available_quantity }}">
                                {{ $item->product->name }} (متاح: {{ number_format($item->available_quantity, 0) }})
                            </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="return_warehouse_id" class="form-label">إلى مخزن *</label>
                    <select name="warehouse_id" id="return_warehouse_id" class="form-control" required>
                        <option value="">-- اختر المخزن --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="return_quantity" class="form-label">الكمية *</label>
                    <input type="number" step="0.01" name="quantity" id="return_quantity" class="form-control"
                           value="{{ old('quantity') }}" min="0.01" required>
                </div>

                <div class="form-group">
                    <label for="return_notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="return_notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-warning" style="width: 100%;">
                    ↩️ استرجاع من المندوب
                </button>
            </form>
        </div>
    </div>
</div>

<!-- المخزون الحالي -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3 class="card-title">📋 المخزون الحالي للمندوب</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الفئة</th>
                    <th>الكمية</th>
                    <th>محجوز</th>
                    <th>متاح</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventory as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                        @if($item->product->sku)
                            <br><small class="text-muted">{{ $item->product->sku }}</small>
                        @endif
                    </td>
                    <td>{{ $item->product->category?->name ?? '-' }}</td>
                    <td>{{ number_format($item->quantity, 0) }}</td>
                    <td>
                        @if($item->reserved_quantity > 0)
                            <span class="text-warning">{{ number_format($item->reserved_quantity, 0) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <strong class="{{ $item->available_quantity <= 5 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($item->available_quantity, 0) }}
                        </strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">لا توجد أصناف مخصصة لهذا المندوب</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- حركات المخزون -->
@if($stockMovements->count() > 0)
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3 class="card-title">🔄 آخر الحركات</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الصنف</th>
                    <th>النوع</th>
                    <th>الكمية</th>
                    <th>المخزن</th>
                    <th>بواسطة</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockMovements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $movement->product->name }}</td>
                    <td>
                        <span class="badge badge-{{ $movement->type_color }}">{{ $movement->type_name }}</span>
                    </td>
                    <td>
                        @if(in_array($movement->type, ['in', 'return']))
                            <span class="text-success">+{{ number_format($movement->quantity, 0) }}</span>
                        @else
                            <span class="text-danger">-{{ number_format($movement->quantity, 0) }}</span>
                        @endif
                    </td>
                    <td>{{ $movement->warehouse?->name ?? '-' }}</td>
                    <td>{{ $movement->createdBy?->name ?? '-' }}</td>
                    <td>{{ $movement->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}
@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
}
.alloc-stock-badge { display: inline-block; min-width: 50px; text-align: center; padding: 4px 8px; font-size: 12px; }
.alloc-stock-ok { background: #dcfce7 !important; color: #166534 !important; }
.alloc-stock-low { background: #fef3c7 !important; color: #92400e !important; }
.alloc-stock-out { background: #fee2e2 !important; color: #991b1b !important; }
.alloc-qty-warning { border-color: #ef4444 !important; background-color: #fef2f2 !important; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Return form logic ===
    document.getElementById('return_product_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const max = selected.dataset.max || 0;
        document.getElementById('return_quantity').max = max;
        document.getElementById('return_quantity').value = max;
    });

    // === Allocation multi-item logic ===
    let allocRowIndex = 1;
    let allocProductsCache = [];

    const warehouseSelect = document.getElementById('warehouse_id');
    const allocAddRowBtn = document.getElementById('allocAddRowBtn');

    // Fetch products when warehouse changes
    warehouseSelect.addEventListener('change', function() {
        const warehouseId = this.value;
        if (!warehouseId) {
            allocProductsCache = [];
            allocAddRowBtn.disabled = true;
            updateAllocProductSelects();
            return;
        }

        fetch(`/warehouses/${warehouseId}/products-with-stock`)
            .then(r => r.json())
            .then(products => {
                allocProductsCache = products;
                allocAddRowBtn.disabled = false;
                updateAllocProductSelects();
            });
    });

    function initAllocSelect2() {
        $('.alloc-product-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ placeholder: 'ابحث عن الصنف...', allowClear: true, dir: 'rtl', width: '100%' })
                .on('change', function() {
                    const row = this.closest('.allocate-row');
                    if (row) updateAllocStockDisplay(row);
                });
            }
        });
    }

    function updateAllocProductSelects() {
        $('.alloc-product-select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });

        document.querySelectorAll('.alloc-product-select').forEach(select => {
            const currentVal = select.value;
            select.innerHTML = '<option value="">اختر الصنف</option>';
            allocProductsCache.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                opt.dataset.available = p.available;
                if (p.id == currentVal) opt.selected = true;
                select.appendChild(opt);
            });
            updateAllocStockDisplay(select.closest('.allocate-row'));
        });

        initAllocSelect2();
    }

    function updateAllocStockDisplay(row) {
        const select = row.querySelector('.alloc-product-select');
        const badge = row.querySelector('.alloc-stock-badge');
        const selectedOption = select.options[select.selectedIndex];

        if (!select.value || !selectedOption.dataset.available) {
            badge.textContent = '-';
            badge.className = 'alloc-stock-badge badge badge-secondary';
            return;
        }

        const available = parseFloat(selectedOption.dataset.available);
        badge.textContent = Math.floor(available);
        badge.dataset.stock = available;

        if (available <= 0) badge.className = 'alloc-stock-badge badge alloc-stock-out';
        else if (available < 10) badge.className = 'alloc-stock-badge badge alloc-stock-low';
        else badge.className = 'alloc-stock-badge badge alloc-stock-ok';
    }

    function attachAllocRowEvents(row) {
        row.querySelector('.alloc-product-select').addEventListener('change', () => updateAllocStockDisplay(row));
        row.querySelector('.alloc-quantity-input').addEventListener('input', function() {
            const badge = row.querySelector('.alloc-stock-badge');
            const stock = parseFloat(badge.dataset.stock) || 0;
            const qty = parseFloat(this.value) || 0;
            this.classList.toggle('alloc-qty-warning', qty > stock && stock > 0);
        });
    }

    // Add row
    allocAddRowBtn.addEventListener('click', function() {
        const tbody = document.getElementById('allocateItemsBody');
        const firstRow = document.querySelector('.allocate-row');

        // Destroy Select2 before cloning
        const $firstSelect = $(firstRow).find('.alloc-product-select');
        if ($firstSelect.hasClass('select2-hidden-accessible')) {
            $firstSelect.select2('destroy');
        }

        const newRow = firstRow.cloneNode(true);

        newRow.setAttribute('data-index', allocRowIndex);
        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, '[' + allocRowIndex + ']');
            if (input.classList.contains('alloc-quantity-input')) input.value = 1;
            else if (input.classList.contains('alloc-product-select')) input.value = '';
        });

        const badge = newRow.querySelector('.alloc-stock-badge');
        badge.textContent = '-';
        badge.className = 'alloc-stock-badge badge badge-secondary';
        newRow.querySelector('.alloc-quantity-input').classList.remove('alloc-qty-warning');
        newRow.querySelector('.alloc-remove-row').style.display = 'inline-block';

        tbody.appendChild(newRow);
        allocRowIndex++;
        updateAllocRemoveButtons();
        attachAllocRowEvents(newRow);
        updateAllocProductSelects();
    });

    // Remove row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('alloc-remove-row')) {
            e.target.closest('.allocate-row').remove();
            updateAllocRemoveButtons();
        }
    });

    function updateAllocRemoveButtons() {
        const rows = document.querySelectorAll('.allocate-row');
        rows.forEach(row => {
            row.querySelector('.alloc-remove-row').style.display = rows.length > 1 ? 'inline-block' : 'none';
        });
    }

    // Initial event setup
    attachAllocRowEvents(document.querySelector('.allocate-row'));
    initAllocSelect2();
});
</script>
@endpush
