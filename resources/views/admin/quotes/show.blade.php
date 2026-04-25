@extends('layouts.app')

@section('title', $quote->quote_number)

@section('content')
@php
    $statusLabels = [
        'draft'      => ['مسودة', 'secondary'],
        'submitted'  => ['قيد الاعتماد', 'warning'],
        'approved'   => ['معتمد', 'success'],
        'rejected'   => ['مرفوض', 'danger'],
        'converted'  => ['محول لفاتورة', 'info'],
    ];
    [$label, $color] = $statusLabels[$quote->status] ?? [$quote->status, 'secondary'];
@endphp

<div class="page-header">
    <div>
        <h1>📑 عرض سعر — {{ $quote->quote_number }}</h1>
        <p>الحالة: <span class="badge badge-{{ $color }}">{{ $label }}</span></p>
    </div>
    <div class="header-actions">
        @can('update', $quote)
            <a href="{{ route('admin.quotes.edit', $quote) }}" class="btn btn-secondary">تعديل</a>
        @endcan

        @can('submit', $quote)
            <form method="POST" action="{{ route('admin.quotes.submit', $quote) }}" style="display:inline">
                @csrf
                <button class="btn btn-primary" onclick="return confirm('تأكيد التقديم للاعتماد؟')">
                    📤 تقديم للاعتماد
                </button>
            </form>
        @endcan

        @can('approve', $quote)
            <form method="POST" action="{{ route('admin.quotes.approve', $quote) }}" style="display:inline">
                @csrf
                <button class="btn btn-success" onclick="return confirm('تأكيد اعتماد عرض السعر؟')">
                    ✅ اعتماد
                </button>
            </form>
        @endcan

        @can('reject', $quote)
            <form method="POST" action="{{ route('admin.quotes.reject', $quote) }}" style="display:inline-flex;gap:.5rem">
                @csrf
                <input type="text" name="rejection_reason" placeholder="سبب الرفض" class="form-control" required>
                <button class="btn btn-danger">❌ رفض</button>
            </form>
        @endcan
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
@endif

<div class="card">
    <h3>التفاصيل</h3>
    <table class="table">
        <tr><th>العميل</th><td>{{ $quote->customer?->name }}</td></tr>
        <tr><th>اسم الفعالية</th><td>{{ $quote->event_name }}</td></tr>
        <tr><th>تاريخ البدء</th><td>{{ $quote->event_start_date?->format('Y-m-d') ?? '—' }}</td></tr>
        <tr><th>تاريخ الانتهاء</th><td>{{ $quote->event_end_date?->format('Y-m-d') ?? '—' }}</td></tr>
        <tr><th>الموقع</th><td>{{ $quote->event_location ?? '—' }}</td></tr>
        <tr><th>نوع الفعالية</th><td>{{ $quote->event_type ?? '—' }}</td></tr>
        <tr><th>صالح حتى</th><td>{{ $quote->valid_until?->format('Y-m-d') ?? '—' }}</td></tr>
        <tr><th>أنشأه</th><td>{{ $quote->creator?->name }}</td></tr>
        @if ($quote->approved_at)
            <tr><th>اعتماد/رفض بواسطة</th><td>{{ $quote->approver?->name }} في {{ $quote->approved_at->format('Y-m-d H:i') }}</td></tr>
        @endif
        @if ($quote->rejection_reason)
            <tr><th>سبب الرفض</th><td>{{ $quote->rejection_reason }}</td></tr>
        @endif
        @if ($quote->notes)
            <tr><th>ملاحظات</th><td>{{ $quote->notes }}</td></tr>
        @endif
    </table>
</div>

<div class="card">
    <h3>البنود</h3>
    <table class="table">
        <thead>
            <tr>
                <th>الخدمة</th>
                <th>الوصف</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الخصم</th>
                <th>الضريبة %</th>
                <th>قيمة الضريبة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->items as $item)
                <tr>
                    <td>{{ $item->service?->name }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->discount_amount, 2) }}</td>
                    <td>{{ rtrim(rtrim((string) $item->tax_rate, '0'), '.') }}</td>
                    <td>{{ number_format((float) $item->tax_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="7" class="text-end">الإجمالي قبل الضريبة:</td>
                <td>{{ number_format((float) $quote->subtotal, 2) }}</td></tr>
            <tr><td colspan="7" class="text-end">إجمالي الخصومات:</td>
                <td>{{ number_format((float) $quote->discount_total, 2) }}</td></tr>
            <tr><td colspan="7" class="text-end">إجمالي الضريبة:</td>
                <td>{{ number_format((float) $quote->tax_total, 2) }}</td></tr>
            <tr><td colspan="7" class="text-end"><strong>الإجمالي النهائي:</strong></td>
                <td><strong>{{ number_format((float) $quote->grand_total, 2) }}</strong></td></tr>
        </tfoot>
    </table>
</div>
@endsection
