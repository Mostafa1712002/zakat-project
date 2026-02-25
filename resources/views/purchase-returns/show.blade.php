@extends('layouts.app')

@section('title', 'عرض مرتجع مشتريات')

@section('content')
<div class="page-header">
    <div>
        <h1>↩️ مرتجع مشتريات - {{ $purchaseReturn->return_number }}</h1>
    </div>
    <div class="header-actions">
        <a href="{{ route('purchases.show', $purchaseReturn->purchase_id) }}" class="btn">📋 الفاتورة الأصلية</a>
        <form action="{{ route('purchase-returns.destroy', $purchaseReturn) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف المرتجع؟ سيتم استعادة المخزون والأرصدة.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">🗑️ حذف المرتجع</button>
        </form>
        <a href="{{ route('purchase-returns.index') }}" class="btn">← رجوع للمرتجعات</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>📋 بيانات المرتجع</h3>
            <table class="info-table">
                <tr>
                    <td class="info-label">رقم المرتجع:</td>
                    <td class="info-value"><strong>{{ $purchaseReturn->return_number }}</strong></td>
                </tr>
                <tr>
                    <td class="info-label">تاريخ المرتجع:</td>
                    <td class="info-value">{{ $purchaseReturn->return_date?->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td class="info-label">الحالة:</td>
                    <td class="info-value"><span class="badge badge-success">مكتمل</span></td>
                </tr>
                @if($purchaseReturn->reason)
                <tr>
                    <td class="info-label">سبب الإرجاع:</td>
                    <td class="info-value">{{ $purchaseReturn->reason }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3>🏢 بيانات الفاتورة والمورد</h3>
            <table class="info-table">
                <tr>
                    <td class="info-label">رقم الفاتورة:</td>
                    <td class="info-value">
                        <a href="{{ route('purchases.show', $purchaseReturn->purchase_id) }}">
                            {{ $purchaseReturn->purchase->invoice_number ?? '-' }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="info-label">المورد:</td>
                    <td class="info-value"><strong>{{ $purchaseReturn->supplier->name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td class="info-label">المخزن:</td>
                    <td class="info-value">{{ $purchaseReturn->warehouse->name ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>🛒 الأصناف المرتجعة</h3>
        <div class="table-container overflow-auto">
            <table class="table text-nowrap">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصنف</th>
                        <th>الكمية المرتجعة</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                        <th>السبب</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseReturn->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item->product->name ?? '-' }}</strong></td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_cost, 2) }} ج.م</td>
                        <td><strong>{{ number_format($item->total, 2) }} ج.م</strong></td>
                        <td>{{ $item->reason ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="grand-total-row">
                        <td colspan="4" class="text-left"><strong>إجمالي المرتجع</strong></td>
                        <td><strong>{{ number_format($purchaseReturn->total_amount, 2) }} ج.م</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if($purchaseReturn->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $purchaseReturn->notes }}</p>
    </div>
</div>
@endif

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; }
.info-table { width: 100%; }
.info-table tr td { padding: 8px 0; vertical-align: top; }
.info-label { color: #6b7280; width: 40%; font-size: 0.9rem; }
.info-value { color: #111827; font-size: 0.9rem; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-success { background: #10b981; color: white; }
.grand-total-row { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); }
.grand-total-row td { border-top: 2px solid var(--primary) !important; padding: 12px 8px !important; font-size: 1.05rem; }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>
@endsection
