@extends('layouts.app')

@section('title', 'عرض فاتورة الشراء')

@section('content')
<div class="page-header no-print">
    <div>
        <h1>🛒 {{ $purchase->invoice_number }}</h1>
        <p>تفاصيل فاتورة الشراء</p>
    </div>
    <div class="header-actions">
        <button onclick="window.print()" class="btn btn-primary">🖨️ طباعة</button>
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn">تعديل</a>
        <a href="{{ route('purchases.index') }}" class="btn">← رجوع</a>
    </div>
</div>

{{-- Print Header --}}
<div class="purchase-print-header">
    <div class="purchase-print-logo">
        @if(!empty($companyLogo))
            <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $companyName ?: 'الشركة' }}">
        @else
            <img src="{{ asset('logo.png') }}" alt="{{ $companyName ?: 'الشركة' }}">
        @endif
    </div>
    <div class="purchase-print-title">
        @if(!empty($companyName))
            <h2>{{ $companyName }}</h2>
        @endif
        <h3>فاتورة مشتريات</h3>
        <div class="purchase-print-number">{{ $purchase->invoice_number }}</div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات المورد</h3>
            <p><strong>الاسم:</strong> {{ $purchase->supplier->name ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $purchase->supplier->phone ?? '-' }}</p>
            <p><strong>العنوان:</strong> {{ $purchase->supplier->address ?? '-' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>بيانات الفاتورة</h3>
            <p><strong>التاريخ:</strong> {{ $purchase->invoice_date?->format('Y-m-d') }}</p>
            <p><strong>تاريخ الاستحقاق:</strong> {{ $purchase->due_date?->format('Y-m-d') ?? '-' }}</p>
            <p><strong>المستودع:</strong> {{ $purchase->warehouse->name ?? '-' }}</p>
            <p><strong>رقم فاتورة المورد:</strong> {{ $purchase->supplier_invoice_number ?? '-' }}</p>
            <p><strong>الحالة:</strong>
                @switch($purchase->status)
                    @case('draft')<span class="badge badge-secondary">مسودة</span>@break
                    @case('ordered')<span class="badge badge-primary">تم الطلب</span>@break
                    @case('received')<span class="badge badge-success">مستلم</span>@break
                    @case('cancelled')<span class="badge badge-danger">ملغي</span>@break
                @endswitch
            </p>
            <p><strong>حالة الدفع:</strong>
                @switch($purchase->payment_status)
                    @case('unpaid')<span class="badge badge-danger">غير مدفوعة</span>@break
                    @case('partial')<span class="badge badge-warning">جزئي</span>@break
                    @case('paid')<span class="badge badge-success">مدفوعة</span>@break
                @endswitch
            </p>
        </div>
    </div>
</div>

<div class="card overflow-auto" style="margin-top: 1.5rem;">
    <div class="table-container text-nowrap">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                    <td>{{ number_format($item->subtotal, 2) }} ج.م</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-left"><strong>الإجمالي الفرعي</strong></td>
                    <td><strong>{{ number_format($purchase->subtotal, 2) }} ج.م</strong></td>
                </tr>
                @if($purchase->discount_amount > 0)
                <tr>
                    <td colspan="4" class="text-left">الخصم</td>
                    <td>- {{ number_format($purchase->discount_amount, 2) }} ج.م</td>
                </tr>
                @endif
                @if($purchase->shipping_amount > 0)
                <tr>
                    <td colspan="4" class="text-left">الشحن</td>
                    <td>{{ number_format($purchase->shipping_amount, 2) }} ج.م</td>
                </tr>
                @endif
                <tr>
                    <td colspan="4" class="text-left"><strong>الإجمالي النهائي</strong></td>
                    <td><strong style="color: var(--primary); font-size: 1.25rem;">{{ number_format($purchase->total_amount, 2) }} ج.م</strong></td>
                </tr>
                @if($purchase->paid_amount > 0)
                <tr>
                    <td colspan="4" class="text-left">المدفوع</td>
                    <td>{{ number_format($purchase->paid_amount, 2) }} ج.م</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-left">المتبقي</td>
                    <td>{{ number_format($purchase->remaining_amount, 2) }} ج.م</td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>

{{-- Stamp & Signatures for Print --}}
<div class="purchase-signatures">
    <div class="purchase-sig-box">
        <div class="purchase-sig-line"></div>
        <span>توقيع المورد</span>
    </div>
    @if(!empty($companyStamp))
    <div class="purchase-stamp-box">
        <img src="{{ asset('storage/' . $companyStamp) }}" alt="ختم الشركة" class="purchase-stamp-img">
    </div>
    @endif
    <div class="purchase-sig-box">
        <div class="purchase-sig-line"></div>
        <span>توقيع المستلم</span>
    </div>
</div>

@if($purchase->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $purchase->notes }}</p>
    </div>
</div>
@endif

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-primary { background: var(--primary); color: white; }
.badge-success { background: #10b981; color: white; }
.badge-warning { background: #f59e0b; color: white; }
.badge-danger { background: #ef4444; color: white; }
.badge-secondary { background: #6b7280; color: white; }

/* Print header - hidden on screen */
.purchase-print-header { display: none; }

/* Signatures - hidden on screen, shown on print */
.purchase-signatures { display: none; }

@media print {
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    .no-print, .sidebar, .menu-toggle { display: none !important; }
    .main { margin-right: 0 !important; padding: 10px !important; width: 100% !important; max-width: 100% !important; }
    .app { overflow: visible !important; }

    .purchase-print-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 12px;
        margin-bottom: 16px;
        border-bottom: 2px solid #0891b2;
    }
    .purchase-print-logo img {
        max-width: 80px;
        max-height: 60px;
        object-fit: contain;
    }
    .purchase-print-title { text-align: left; }
    .purchase-print-title h2 { font-size: 14pt; color: #0891b2; margin: 0; }
    .purchase-print-title h3 { font-size: 11pt; color: #374151; margin: 4px 0; }
    .purchase-print-number { font-size: 10pt; font-weight: 700; color: #0891b2; }

    .purchase-signatures {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-top: 24px;
        padding-top: 12px;
        border-top: 1px dashed #d1d5db;
    }
    .purchase-sig-box { text-align: center; flex: 1; }
    .purchase-sig-line { border-bottom: 1px solid #374151; height: 40px; margin-bottom: 6px; }
    .purchase-sig-box span { font-size: 9pt; color: #6b7280; }
    .purchase-stamp-box { display: flex; align-items: center; justify-content: center; }
    .purchase-stamp-img { max-width: 70px; max-height: 70px; object-fit: contain; opacity: 0.85; }

    @page { size: A4 portrait; margin: 10mm; }
}
</style>
@endsection
