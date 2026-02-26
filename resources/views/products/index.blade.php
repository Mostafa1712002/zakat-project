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

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الكود..." value="{{ request('search') }}">
                <select name="supplier_id" class="form-control">
                    <option value="">كل الموردين</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                <select name="stock_status" class="form-control">
                    <option value="">كل حالات المخزون</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>متوفر</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>منخفض</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>نفذ</option>
                </select>
                <button type="submit" class="btn btn-primary">بحث</button>
                <a href="{{ route('products.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
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
    <div class="card-footer">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
