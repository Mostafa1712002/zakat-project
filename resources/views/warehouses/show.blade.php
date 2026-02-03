@extends('layouts.app')

@section('title', 'عرض المخزن')

@section('content')
<div class="page-header">
    <div>
        <h1>🏭 {{ $warehouse->name }}</h1>
        <p>تفاصيل المخزن والمخزون الحالي</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn">تعديل</a>
        <a href="{{ route('warehouses.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات المخزن</h3>
            <p><strong>الكود:</strong> {{ $warehouse->code ?? '-' }}</p>
            <p><strong>الفرع:</strong> {{ $warehouse->branch->name ?? '-' }}</p>
            <p><strong>المدير:</strong> {{ $warehouse->manager_name ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $warehouse->phone ?? '-' }}</p>
            <p><strong>العنوان:</strong> {{ $warehouse->address ?? '-' }}</p>
            <p><strong>الحالة:</strong>
                @if($warehouse->is_active)
                    <span class="badge badge-success">نشط</span>
                @else
                    <span class="badge badge-danger">غير نشط</span>
                @endif
            </p>
            <p><strong>مخزن افتراضي:</strong> {{ $warehouse->is_default ? 'نعم' : 'لا' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>ملخص المخزون</h3>
            <p><strong>عدد الأصناف:</strong> {{ $warehouse->inventoryLevels->count() }}</p>
            <p><strong>إجمالي الكمية:</strong> {{ number_format($warehouse->inventoryLevels->sum('quantity'), 2) }}</p>
            <p><strong>إجمالي الكمية المتاحة:</strong> {{ number_format($warehouse->inventoryLevels->sum('available_quantity'), 2) }}</p>
            <p><strong>قيمة المخزون:</strong> {{ number_format($warehouse->total_stock_value, 2) }} ج.م</p>
        </div>
    </div>
</div>

@if($warehouse->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $warehouse->notes }}</p>
    </div>
</div>
@endif

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2>📦 المخزون الحالي</h2>
        <div class="search-box">
            <input type="text" id="productSearch" class="form-control" placeholder="🔍 بحث بإسم الصنف..." style="width: 250px;">
        </div>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الكمية</th>
                    <th>المحجوز</th>
                    <th>المتاح</th>
                    <th>الحد الأدنى</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouse->inventoryLevels as $index => $level)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $level->product->name ?? '-' }}</strong></td>
                    <td>{{ number_format($level->quantity, 2) }}</td>
                    <td>{{ number_format($level->reserved_quantity, 2) }}</td>
                    <td>{{ number_format($level->available_quantity, 2) }}</td>
                    <td>{{ $level->min_stock ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <h3>لا يوجد مخزون</h3>
                            <p>لم يتم تسجيل أي مستويات مخزون لهذا المخزن بعد</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
.search-box input { padding: 8px 12px; font-size: 14px; }
.highlight { background-color: #fef08a; }
</style>

@push('scripts')
<script>
document.getElementById('productSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.table tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const productName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        if (searchTerm === '' || productName.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Show "no results" message if no matches
    const emptyRow = document.querySelector('.table tbody .empty-state');
    if (visibleCount === 0 && !emptyRow && searchTerm !== '') {
        const tbody = document.querySelector('.table tbody');
        const existingNoResults = document.getElementById('noSearchResults');
        if (!existingNoResults) {
            const noResultsRow = document.createElement('tr');
            noResultsRow.id = 'noSearchResults';
            noResultsRow.innerHTML = '<td colspan="6" class="text-center text-muted">لا توجد نتائج للبحث</td>';
            tbody.appendChild(noResultsRow);
        }
    } else {
        const existingNoResults = document.getElementById('noSearchResults');
        if (existingNoResults) existingNoResults.remove();
    }
});
</script>
@endpush
@endsection
