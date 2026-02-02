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
    <!-- نموذج تخصيص صنف -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">➕ تخصيص صنف للمندوب</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sales-rep-inventory.allocate', $salesRep) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="product_id" class="form-label">الصنف *</label>
                    <select name="product_id" id="product_id" class="form-control" required>
                        <option value="">-- اختر صنف --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} {{ $product->sku ? "({$product->sku})" : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="warehouse_id" class="form-label">من مخزن *</label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                        <option value="">-- اختر المخزن --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="quantity" class="form-label">الكمية *</label>
                    <input type="number" step="0.01" name="quantity" id="quantity" class="form-control"
                           value="{{ old('quantity') }}" min="0.01" required>
                    @error('quantity')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
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
    <div class="table-container">
        <table class="table">
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
    <div class="table-container">
        <table class="table">
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
</style>
@endpush

@push('scripts')
<script>
document.getElementById('return_product_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const max = selected.dataset.max || 0;
    document.getElementById('return_quantity').max = max;
    document.getElementById('return_quantity').value = max;
});
</script>
@endpush
