@extends('layouts.app')

@section('title', 'عرض الصنف')

@section('content')
<div class="page-header">
    <div>
        <h1>📦 {{ $product->name }}</h1>
        <p>تفاصيل الصنف والمخزون</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('products.edit', $product) }}" class="btn">تعديل</a>
        <a href="{{ route('products.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات الصنف</h3>
            <p><strong>القسم:</strong> {{ $product->category->name ?? '-' }}</p>
            <p><strong>الوحدة:</strong> {{ $product->unit->name ?? '-' }}</p>
            <p><strong>SKU:</strong> {{ $product->sku ?? '-' }}</p>
            <p><strong>الباركود:</strong> {{ $product->barcode ?? '-' }}</p>
            <p><strong>الوصف:</strong> {{ $product->description ?? '-' }}</p>
            <p><strong>التتبع المخزني:</strong> {{ $product->track_inventory ? 'نعم' : 'لا' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>الأسعار</h3>
            <p><strong>سعر البيع:</strong> {{ number_format($product->selling_price, 2) }} ج.م</p>
            <p><strong>سعر التكلفة:</strong> {{ number_format($product->cost_price, 2) }} ج.م</p>
            <p><strong>أقل سعر بيع:</strong> {{ number_format($product->min_selling_price ?? 0, 2) }} ج.م</p>
            <p><strong>سعر الجملة:</strong> {{ number_format($product->wholesale_price ?? 0, 2) }} ج.م</p>
            <p><strong>الضريبة:</strong> {{ $product->is_taxable ? $product->tax_rate . '%' : 'غير خاضع' }}</p>
        </div>
    </div>
</div>

@if($product->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $product->notes }}</p>
    </div>
</div>
@endif

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2>🏭 المخزون حسب المخزن</h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المخزن</th>
                    <th>الكمية</th>
                    <th>المحجوز</th>
                    <th>المتاح</th>
                </tr>
            </thead>
            <tbody>
                @forelse($product->inventoryLevels as $index => $level)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $level->warehouse->name ?? '-' }}</td>
                    <td>{{ number_format($level->quantity, 2) }}</td>
                    <td>{{ number_format($level->reserved_quantity, 2) }}</td>
                    <td>{{ number_format($level->available_quantity, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <h3>المخزون الحالي: 0</h3>
                            <p>قم بإنشاء فاتورة شراء لإضافة مخزون لهذا الصنف</p>
                            <a href="{{ route('purchases.create') }}" class="btn btn-primary" style="margin-top: 12px;">+ فاتورة شراء جديدة</a>
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
</style>
@endsection
