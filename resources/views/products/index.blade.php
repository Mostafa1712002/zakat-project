@extends('layouts.app')

@section('title', 'الأصناف')

@section('content')
<div class="page-header">
    <div>
        <h1>📦 الأصناف</h1>
        <p>إدارة المنتجات والأصناف</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ إضافة صنف</a>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>المورد</th>
                    <th>الكود</th>
                    <th>سعر البيع</th>
                    <th>المخزون</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td>{{ $product->supplier->name ?? '-' }}</td>
                    <td><code>{{ $product->sku ?? '-' }}</code></td>
                    <td>{{ number_format($product->selling_price, 2) }} ج.م</td>
                    <td>
                        @php $stock = $product->stock_quantity ?? 0; @endphp
                        @if(!$product->track_inventory)
                            <span class="badge text-muted">غير متتبع</span>
                        @elseif($stock <= 0)
                            <span class="badge badge-danger">نفذ</span>
                        @elseif($product->min_stock !== null && $stock <= $product->min_stock)
                            <span class="badge badge-warning">{{ number_format($stock, 2) }}</span>
                        @else
                            <span class="badge badge-success">{{ number_format($stock, 2) }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm">عرض</a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <h3>لا توجد أصناف</h3>
                            <p>ابدأ بإضافة صنف جديد</p>
                            <a href="{{ route('products.create') }}" class="btn btn-primary">+ إضافة صنف</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="pagination">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
