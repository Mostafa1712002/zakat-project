@extends('layouts.app')

@section('title', 'عرض التسعيرة')

@section('content')
<div class="invoice-wrapper">
    <!-- Action Buttons -->
    <div class="invoice-actions no-print">
        <a href="{{ route('quotations.pdf', $sale) }}" target="_blank" class="btn btn-primary btn-lg">
            <span class="btn-icon">🖨️</span>
            طباعة
        </a>
        @if($sale->status === 'draft')
        <form action="{{ route('quotations.confirm', $sale) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من تأكيد التسعيرة؟ سيتم تحويلها لفاتورة مبيعات وخصم الكميات من المخزون.')">
            @csrf
            <button type="submit" class="btn btn-success btn-lg">
                <span class="btn-icon">✅</span>
                تحويل لفاتورة
            </button>
        </form>
        <a href="{{ route('quotations.edit', $sale) }}" class="btn">تعديل</a>
        @endif
        <a href="{{ route('quotations.index') }}" class="btn">← رجوع للتسعيرات</a>
    </div>

    <!-- Invoice Container -->
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1 class="company-name">{{ $companyName ?: 'الشركة' }}</h1>
                @if(!empty($invoiceContacts))
                <div class="header-contacts">
                    @foreach($invoiceContacts as $contact)
                    <span class="header-contact-item">{{ $contact['name'] }}: <span dir="ltr">{{ $contact['phone'] }}</span></span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="invoice-title">
                <div class="invoice-title-main">
                    <h2>تسعيرة مبيعات</h2>
                    <div class="invoice-number">{{ $sale->invoice_number }}</div>
                </div>
                <div class="invoice-status no-print">
                    @if($sale->status === 'draft')
                        <span class="badge" style="background: #fefce8; color: #a16207; padding: 4px 12px; border-radius: 20px;">مسودة - عرض سعر</span>
                    @else
                        <span class="badge badge-primary">مؤكدة</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid no-print">
            <div class="info-box">
                <div class="info-box-header">
                    <span class="info-icon">👤</span>
                    <h4>بيانات العميل</h4>
                </div>
                <div class="info-box-body overflow-auto">
                    <table class="info-table text-nowrap">
                        <tr><td class="info-label">الاسم:</td><td class="info-value"><strong>{{ $sale->customer->name ?? '-' }}</strong></td></tr>
                        <tr><td class="info-label">الهاتف:</td><td class="info-value">{{ $sale->customer->phone ?? '-' }}</td></tr>
                        <tr><td class="info-label">العنوان:</td><td class="info-value">{{ $sale->customer->address ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="info-box">
                <div class="info-box-header">
                    <span class="info-icon">📋</span>
                    <h4>بيانات التسعيرة</h4>
                </div>
                <div class="info-box-body overflow-auto">
                    <table class="info-table text-nowrap">
                        <tr><td class="info-label">التاريخ:</td><td class="info-value"><strong>{{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</strong></td></tr>
                        <tr><td class="info-label">صالحة حتى:</td><td class="info-value">{{ $sale->due_date?->format('Y-m-d') ?? '-' }}</td></tr>
                        <tr><td class="info-label">نوع الدفع:</td><td class="info-value">{{ $sale->payment_type === 'credit' ? 'آجل' : 'نقدي' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-card">
            <div class="items-table-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            @if(feature_enabled('grade_system'))
                            <th>الفرز</th>
                            @endif
                            <th>الكمية</th>
                            <th>سعر الوحدة</th>
                            @if(feature_enabled('per_item_discount'))
                            <th>الخصم</th>
                            @endif
                            @if(feature_enabled('tile_area_tracking'))
                            <th>المساحة</th>
                            @endif
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="item-name">{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                            @if(feature_enabled('grade_system'))
                            <td>{{ $item->grade->name_ar ?? $item->grade->name ?? '-' }}</td>
                            @endif
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            @if(feature_enabled('per_item_discount'))
                            <td>{{ number_format($item->discount_amount, 2) }}</td>
                            @endif
                            @if(feature_enabled('tile_area_tracking'))
                            <td>{{ $item->total_area ? number_format($item->total_area, 2) . ' م²' : '-' }}</td>
                            @endif
                            <td class="item-total">{{ number_format($item->total ?? $item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @php
                        $showColCount = 4;
                        if (feature_enabled('grade_system')) $showColCount++;
                        if (feature_enabled('per_item_discount')) $showColCount++;
                        if (feature_enabled('tile_area_tracking')) $showColCount++;
                    @endphp
                    <tfoot>
                        <tr class="subtotal-row">
                            <td colspan="{{ $showColCount }}" class="text-left"><strong>الإجمالي الفرعي</strong></td>
                            <td><strong>{{ number_format($sale->subtotal, 2) }} ج.م</strong></td>
                        </tr>
                        @if($sale->discount_amount > 0)
                        <tr>
                            <td colspan="{{ $showColCount }}" class="text-left">الخصم</td>
                            <td class="discount">- {{ number_format($sale->discount_amount, 2) }} ج.م</td>
                        </tr>
                        @endif
                        <tr class="grand-total-row">
                            <td colspan="{{ $showColCount }}" class="text-left"><strong>الإجمالي النهائي</strong></td>
                            <td class="grand-total"><strong>{{ number_format($sale->total_amount, 2) }} ج.م</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Signatures Section -->
        <div class="signatures-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <span>توقيع العميل</span>
            </div>
            @if(!empty($companyStamp))
            <div class="stamp-box">
                <img src="{{ asset('storage/' . $companyStamp) }}" alt="ختم الشركة" class="stamp-image">
            </div>
            @endif
            <div class="signature-box">
                <div class="signature-line"></div>
                <span>توقيع المندوب</span>
            </div>
        </div>
    </div>

    @if($sale->notes)
    <div class="card no-print" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3>ملاحظات</h3>
            <p>{{ $sale->notes }}</p>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .invoice-wrapper { max-width: 980px; margin: 0 auto; padding: 20px; }
    .invoice-actions { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
    .invoice-actions .btn-lg { padding: 12px 24px; font-size: 1rem; display: flex; align-items: center; gap: 8px; }
    .btn-icon { font-size: 1.2rem; }
    .invoice-container { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; border: 1px solid #e5e7eb; }
    .invoice-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; padding-bottom: 16px; border-bottom: 3px double var(--primary); margin-bottom: 16px; }
    .company-info { flex: 1; text-align: right; }
    .company-name { font-size: 1.8rem; font-weight: bold; color: var(--primary); margin: 0; }
    .invoice-title { flex: 1; text-align: left; }
    .invoice-title-main { display: inline-flex; align-items: baseline; gap: 8px; white-space: nowrap; }
    .invoice-title h2 { font-size: 1.3rem; color: #374151; margin: 0; }
    .invoice-number { font-size: 1.4rem; font-weight: bold; color: var(--primary); }
    .invoice-status { margin-top: 8px; }
    .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border: none; }
    .btn-success:hover { background: linear-gradient(135deg, #16a34a, #15803d); }
    .header-contacts { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 4px; }
    .header-contact-item { font-size: 0.85rem; color: #374151; }
    .header-contact-item span { color: var(--primary); font-weight: 600; }
    .info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin-bottom: 25px; }
    .info-box { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
    .info-box-header { display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e5e7eb; }
    .info-box-header h4 { margin: 0; font-size: 0.95rem; color: var(--primary); }
    .info-icon { font-size: 1.1rem; }
    .info-box-body { padding: 12px 16px; }
    .info-table { width: 100%; }
    .info-table tr td { padding: 6px 0; vertical-align: top; }
    .info-label { color: #6b7280; width: 35%; font-size: 0.9rem; }
    .info-value { color: #111827; font-size: 0.9rem; }
    .items-table-wrap { width: 100%; overflow-x: auto; }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th { background: var(--primary); color: white; padding: 10px 8px; font-size: 0.9rem; text-align: center; font-weight: 600; }
    .items-table th:first-child { border-radius: 8px 0 0 0; }
    .items-table th:last-child { border-radius: 0 8px 0 0; }
    .items-table td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; text-align: center; font-size: 0.9rem; }
    .items-table tbody tr:hover { background: #f9fafb; }
    .item-name { text-align: right !important; font-weight: 500; }
    .item-total { font-weight: 600; color: var(--primary); }
    .items-table tfoot td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
    .discount { color: #dc2626; }
    .grand-total-row { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); }
    .grand-total-row td { border-top: 2px solid var(--primary) !important; padding: 12px 8px !important; }
    .grand-total { font-size: 1.1rem; color: var(--primary); }
    .text-left { text-align: right !important; }
    .signatures-section { display: flex; justify-content: space-between; gap: 24px; margin: 28px 0 10px; padding-top: 14px; border-top: 1px dashed #d1d5db; }
    .signature-box { text-align: center; flex: 1; max-width: 45%; }
    .signature-line { border-bottom: 1px solid #374151; height: 50px; margin-bottom: 8px; }
    .signature-box span { font-size: 0.85rem; color: #6b7280; }
    .stamp-box { display: flex; align-items: center; justify-content: center; }
    .stamp-image { max-width: 100px; max-height: 100px; object-fit: contain; opacity: 0.85; }

    @media (max-width: 768px) {
        .invoice-wrapper { padding: 8px; }
        .invoice-container { padding: 16px; }
        .invoice-actions { flex-direction: column; }
        .invoice-actions .btn, .invoice-actions .btn-lg, .invoice-actions form { width: 100%; }
        .invoice-actions .btn, .invoice-actions .btn-lg { justify-content: center; }
        .invoice-header { align-items: flex-start; gap: 12px; }
        .company-name { font-size: 1.35rem; }
        .info-grid { grid-template-columns: 1fr; gap: 12px; }
        .signatures-section { flex-direction: column; gap: 12px; }
        .signature-box { max-width: none; }
        .items-table { min-width: 600px; }
    }

    @media print {
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .no-print { display: none !important; }
        .main { margin-right: 0 !important; padding: 0 !important; width: 100% !important; }
        .invoice-wrapper { max-width: 100%; padding: 0; margin: 0; }
        .invoice-container { box-shadow: none; border: none; padding: 8mm 10mm; border-radius: 0; }
    }
</style>
@endpush
