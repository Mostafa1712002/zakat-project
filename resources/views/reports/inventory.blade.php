@extends('layouts.app')

@section('title', 'تقرير المخزون')

@section('content')
<div class="page-header">
    <div>
        <h1>📦 تقرير المخزون</h1>
        <p>حالة المخزون والأصناف</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('reports.index') }}" class="btn">← التقارير</a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="form-row form-row-3">
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
                <label class="form-label">القسم</label>
                <select name="category_id" class="form-control">
                    <option value="">كل الأقسام</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string)$categoryId === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">حالة المخزون</label>
                <select name="stock_status" class="form-control">
                    <option value="">الكل</option>
                    <option value="low" {{ $stockStatus === 'low' ? 'selected' : '' }}>منخفض</option>
                    <option value="out" {{ $stockStatus === 'out' ? 'selected' : '' }}>نافذ</option>
                    <option value="normal" {{ $stockStatus === 'normal' ? 'selected' : '' }}>طبيعي</option>
                </select>
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">🔍 عرض التقرير</button>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <div class="stat-value">{{ $totalProducts }}</div>
            <div class="stat-label">عدد الأصناف</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totalQuantity, 2) }}</div>
            <div class="stat-label">إجمالي الكمية</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totalValue, 2) }} ج.م</div>
            <div class="stat-label">قيمة المخزون</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-content">
            <div class="stat-value">{{ $lowStockCount }}</div>
            <div class="stat-label">منخفض</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⛔</div>
        <div class="stat-content">
            <div class="stat-value">{{ $outOfStockCount }}</div>
            <div class="stat-label">نافذ</div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>📋 تفاصيل المخزون</h3>
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>القسم</th>
                    <th>المخزن</th>
                    <th>الكمية</th>
                    <th>المحجوز</th>
                    <th>المتاح</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventoryLevels as $level)
                <tr>
                    <td>{{ $level->product->name ?? '-' }}</td>
                    <td>{{ $level->product->category->name ?? '-' }}</td>
                    <td>{{ $level->warehouse->name ?? '-' }}</td>
                    <td>{{ number_format($level->quantity, 2) }}</td>
                    <td>{{ number_format($level->reserved_quantity, 2) }}</td>
                    <td>{{ number_format($level->available_quantity, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <h3>لا توجد بيانات</h3>
                            <p>لم يتم العثور على أصناف مطابقة للفلترة</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid-2" style="margin-top: 1.5rem;">
    <div class="card">
        <div class="card-header">
            <h3>🏭 المخزون حسب المخزن</h3>
        </div>
        <div class="table-container overflow-auto">
            <table class="table text-nowrap">
                <thead>
                    <tr>
                        <th>المخزن</th>
                        <th>الكمية</th>
                        <th>القيمة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockByWarehouse as $row)
                    <tr>
                        <td>{{ $row['warehouse']->name ?? '-' }}</td>
                        <td>{{ number_format($row['total_quantity'], 2) }}</td>
                        <td>{{ number_format($row['total_value'], 2) }} ج.م</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">لا توجد بيانات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3>🏷️ المخزون حسب القسم</h3>
        </div>
        <div class="table-container overflow-auto">
            <table class="table text-nowrap">
                <thead>
                    <tr>
                        <th>القسم</th>
                        <th>الكمية</th>
                        <th>القيمة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockByCategory as $row)
                    <tr>
                        <td>{{ $row['category']->name ?? '-' }}</td>
                        <td>{{ number_format($row['total_quantity'], 2) }}</td>
                        <td>{{ number_format($row['total_value'], 2) }} ج.م</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">لا توجد بيانات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--card); border-radius: var(--radius); padding: 1rem; display: flex; align-items: center; gap: 0.75rem; box-shadow: var(--shadow); }
.stat-icon { font-size: 2rem; }
.stat-value { font-size: 1.25rem; font-weight: 700; }
.stat-label { font-size: 0.8rem; color: var(--text-muted); }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); }
.card-header h3 { margin: 0; font-size: 1.125rem; }
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }
</style>
@endsection
