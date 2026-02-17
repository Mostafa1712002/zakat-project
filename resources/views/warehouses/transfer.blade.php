@extends('layouts.app')

@section('title', 'تحويل مخزون')

@section('content')
<div class="page-header">
    <div>
        <h1>🔄 تحويل مخزون</h1>
        <p>نقل الأصناف بين المخازن</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('warehouses.index') }}" class="btn">← رجوع للمخازن</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('warehouses.process-transfer') }}" method="POST" id="transferForm">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">من مخزن *</label>
                    <select name="from_warehouse_id" id="from_warehouse_id" class="form-control" required>
                        <option value="">اختر المخزن المصدر</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">إلى مخزن *</label>
                    <select name="to_warehouse_id" id="to_warehouse_id" class="form-control" required>
                        <option value="">اختر المخزن الهدف</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr style="margin: 20px 0;">
            <h3 style="margin-bottom: 16px;">📦 الأصناف</h3>

            <div class="table-container overflow-auto">
                <table class="table text-nowrap" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 45%;">الصنف</th>
                            <th style="width: 15%;">المتاح</th>
                            <th style="width: 25%;">الكمية</th>
                            <th style="width: 15%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row" data-index="0">
                            <td>
                                <select name="items[0][product_id]" class="form-control product-select" required>
                                    <option value="">اختر الصنف</option>
                                </select>
                            </td>
                            <td>
                                <span class="stock-badge badge badge-secondary">-</span>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control quantity-input" value="1" min="0.001" step="0.001" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-row" style="display: none;">×</button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <button type="button" class="btn btn-sm" id="addRowBtn" disabled>+ إضافة صنف</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="سبب التحويل أو أي ملاحظات...">{{ old('notes') }}</textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">🔄 تنفيذ التحويل</button>
                <a href="{{ route('warehouses.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
.stock-badge { display: inline-block; min-width: 50px; text-align: center; padding: 4px 8px; font-size: 12px; }
.stock-ok { background: #dcfce7 !important; color: #166534 !important; }
.stock-low { background: #fef3c7 !important; color: #92400e !important; }
.stock-out { background: #fee2e2 !important; color: #991b1b !important; }
.quantity-warning { border-color: #ef4444 !important; background-color: #fef2f2 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    let productsCache = [];

    const fromWarehouseSelect = document.getElementById('from_warehouse_id');
    const addRowBtn = document.getElementById('addRowBtn');

    // Fetch products when source warehouse changes
    fromWarehouseSelect.addEventListener('change', function() {
        const warehouseId = this.value;
        if (!warehouseId) {
            productsCache = [];
            addRowBtn.disabled = true;
            updateAllProductSelects();
            return;
        }

        fetch(`/warehouses/${warehouseId}/products-with-stock`)
            .then(r => r.json())
            .then(products => {
                productsCache = products;
                addRowBtn.disabled = false;
                updateAllProductSelects();
            });
    });

    function updateAllProductSelects() {
        document.querySelectorAll('.product-select').forEach(select => {
            const currentVal = select.value;
            select.innerHTML = '<option value="">اختر الصنف</option>';
            productsCache.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                opt.dataset.available = p.available;
                if (p.id == currentVal) opt.selected = true;
                select.appendChild(opt);
            });
            // Update stock display for current selection
            updateStockDisplay(select.closest('.item-row'));
        });
    }

    function updateStockDisplay(row) {
        const select = row.querySelector('.product-select');
        const badge = row.querySelector('.stock-badge');
        const selectedOption = select.options[select.selectedIndex];

        if (!select.value || !selectedOption.dataset.available) {
            badge.textContent = '-';
            badge.className = 'stock-badge badge badge-secondary';
            return;
        }

        const available = parseFloat(selectedOption.dataset.available);
        badge.textContent = Math.floor(available);
        badge.dataset.stock = available;

        if (available <= 0) badge.className = 'stock-badge badge stock-out';
        else if (available < 10) badge.className = 'stock-badge badge stock-low';
        else badge.className = 'stock-badge badge stock-ok';
    }

    function attachRowEvents(row) {
        row.querySelector('.product-select').addEventListener('change', () => updateStockDisplay(row));
        row.querySelector('.quantity-input').addEventListener('input', function() {
            const badge = row.querySelector('.stock-badge');
            const stock = parseFloat(badge.dataset.stock) || 0;
            const qty = parseFloat(this.value) || 0;
            this.classList.toggle('quantity-warning', qty > stock && stock > 0);
        });
    }

    // Add row
    addRowBtn.addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const firstRow = document.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.setAttribute('data-index', rowIndex);
        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace('[0]', '[' + rowIndex + ']');
            if (input.classList.contains('quantity-input')) input.value = 1;
            else if (input.classList.contains('product-select')) input.value = '';
        });

        const badge = newRow.querySelector('.stock-badge');
        badge.textContent = '-';
        badge.className = 'stock-badge badge badge-secondary';
        newRow.querySelector('.quantity-input').classList.remove('quantity-warning');
        newRow.querySelector('.remove-row').style.display = 'inline-block';

        tbody.appendChild(newRow);
        rowIndex++;
        updateRemoveButtons();
        attachRowEvents(newRow);
        // Populate the select with cached products
        updateAllProductSelects();
    });

    // Remove row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.item-row').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            row.querySelector('.remove-row').style.display = rows.length > 1 ? 'inline-block' : 'none';
        });
    }

    // Initial event setup
    attachRowEvents(document.querySelector('.item-row'));
});
</script>
@endsection
