@extends('layouts.app')

@section('title', 'مخزني')

@section('content')
<div class="page-header">
    <div>
        <h1>📦 مخزني</h1>
        <p>الأصناف المتاحة للبيع</p>
    </div>
</div>

<!-- إحصائيات المخزون -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">📦</div>
        <div class="stat-details">
            <div class="stat-value">{{ $inventory->count() }}</div>
            <div class="stat-label">عدد الأصناف</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">🔢</div>
        <div class="stat-details">
            <div class="stat-value">{{ number_format($inventory->sum('quantity'), 0) }}</div>
            <div class="stat-label">إجمالي الوحدات</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">⚠️</div>
        <div class="stat-details">
            <div class="stat-value">{{ $inventory->where('quantity', '<=', 5)->count() }}</div>
            <div class="stat-label">أصناف منخفضة</div>
        </div>
    </div>
</div>

<!-- قائمة المخزون -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 الأصناف المتاحة</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الفئة</th>
                    <th>الكمية المتاحة</th>
                    <th>محجوز</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventory as $item)
                <tr class="{{ $item->quantity <= 5 ? 'low-stock-row' : '' }}">
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                        @if($item->product->sku)
                            <br><small class="text-muted">{{ $item->product->sku }}</small>
                        @endif
                    </td>
                    <td>{{ $item->product->category?->name ?? '-' }}</td>
                    <td>
                        <strong class="{{ $item->available_quantity <= 5 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($item->available_quantity, 0) }}
                        </strong>
                        <small>{{ $item->product->unit ?? 'وحدة' }}</small>
                    </td>
                    <td>
                        @if($item->reserved_quantity > 0)
                            <span class="text-warning">{{ number_format($item->reserved_quantity, 0) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($item->quantity <= 0)
                            <span class="badge badge-danger">نفذ</span>
                        @elseif($item->quantity <= 5)
                            <span class="badge badge-warning">منخفض</span>
                        @else
                            <span class="badge badge-success">متوفر</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        <div style="padding: 40px;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
                            <p>لا توجد أصناف مخصصة لك بعد</p>
                            <small>تواصل مع الإدارة لتخصيص الأصناف</small>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- آخر حركات المخزون -->
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
.low-stock-row {
    background-color: #fef2f2;
}
</style>
@endpush
