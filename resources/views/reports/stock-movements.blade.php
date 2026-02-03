@extends('layouts.app')

@section('title', 'تقرير حركة المخزون')

@section('content')
<div class="page-header">
    <div>
        <h1>🔄 تقرير حركة المخزون</h1>
        <p>سجل التحركات حسب المخزن والصنف</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('reports.index') }}" class="btn">← التقارير</a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="form-group">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="form-group">
                <label class="form-label">المخزن</label>
                <select name="warehouse_id" class="form-control">
                    <option value="">كل المخازن</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ (string)$warehouseId === (string)$warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">الصنف</label>
                <select name="product_id" class="form-control">
                    <option value="">كل الأصناف</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ (string)$productId === (string)$product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">نوع الحركة</label>
                <select name="type" class="form-control">
                    <option value="">كل الأنواع</option>
                    <option value="in" {{ $type === 'in' ? 'selected' : '' }}>وارد</option>
                    <option value="out" {{ $type === 'out' ? 'selected' : '' }}>صادر</option>
                    <option value="transfer" {{ $type === 'transfer' ? 'selected' : '' }}>تحويل</option>
                    <option value="adjustment" {{ $type === 'adjustment' ? 'selected' : '' }}>تسوية</option>
                    <option value="return" {{ $type === 'return' ? 'selected' : '' }}>مرتجع</option>
                </select>
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">🔍 عرض التقرير</button>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid">
    @foreach(['in' => 'وارد', 'out' => 'صادر', 'transfer' => 'تحويل', 'adjustment' => 'تسوية', 'return' => 'مرتجع'] as $key => $label)
        @php $summary = $summaryByType[$key] ?? null; @endphp
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <div class="stat-value">{{ $summary->count ?? 0 }}</div>
                <div class="stat-label">{{ $label }} ({{ number_format($summary->total_quantity ?? 0, 2) }})</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>📋 سجل الحركات</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>الصنف</th>
                    <th>المخزن</th>
                    <th>إلى مخزن</th>
                    <th>النوع</th>
                    <th>الكمية</th>
                    <th>المستخدم</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $index => $movement)
                <tr>
                    <td>{{ $movements->firstItem() + $index }}</td>
                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $movement->product->name ?? '-' }}</td>
                    <td>{{ $movement->warehouse->name ?? '-' }}</td>
                    <td>{{ $movement->toWarehouse->name ?? '-' }}</td>
                    <td>{{ $movement->type }}</td>
                    <td>{{ number_format($movement->quantity, 2) }}</td>
                    <td>{{ $movement->user->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">🔄</div>
                            <h3>لا توجد حركات</h3>
                            <p>لم يتم تسجيل أي حركة خلال الفترة المحددة</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($movements->hasPages())
    <div class="pagination">
        {{ $movements->withQueryString()->links() }}
    </div>
    @endif
</div>

<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--card); border-radius: var(--radius); padding: 1rem; display: flex; align-items: center; gap: 0.75rem; box-shadow: var(--shadow); }
.stat-icon { font-size: 2rem; }
.stat-value { font-size: 1.25rem; font-weight: 700; }
.stat-label { font-size: 0.8rem; color: var(--text-muted); }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); }
.card-header h3 { margin: 0; font-size: 1.125rem; }
</style>
@endsection
