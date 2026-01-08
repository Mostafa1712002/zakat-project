@extends('layouts.app')

@section('title', 'عرض القسم')

@section('content')
<div class="page-header">
    <div>
        <h1>🏷️ {{ $category->name }}</h1>
        <p>تفاصيل القسم والأصناف التابعة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('categories.edit', $category) }}" class="btn">تعديل</a>
        <a href="{{ route('categories.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3>معلومات القسم</h3>
        <p><strong>الوصف:</strong> {{ $category->description ?? '-' }}</p>
        <p><strong>عدد الأصناف:</strong> {{ $category->products->count() }}</p>
        <p><strong>تاريخ الإنشاء:</strong> {{ $category->created_at->format('Y/m/d') }}</p>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2>📦 الأصناف</h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الكود</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                @forelse($category->products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td><code>{{ $product->sku ?? $product->barcode ?? '-' }}</code></td>
                    <td>{{ number_format($product->selling_price, 2) }} ج.م</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <h3>لا توجد أصناف</h3>
                            <p>يمكنك إضافة أصناف جديدة لهذا القسم</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
</style>
@endsection
