@extends('layouts.app')

@section('title', 'لوحة متابعة الفوترة الإلكترونية')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">لوحة متابعة الفوترة الإلكترونية (ZATCA)</h4>

    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['pending_clearance'] }}</h3>
                    <small>بانتظار الاعتماد</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['pending_reporting'] }}</h3>
                    <small>بانتظار الرفع</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['cleared'] }}</h3>
                    <small>تم الاعتماد</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['reported'] }}</h3>
                    <small>تم الرفع</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['failed'] }}</h3>
                    <small>فشل</small>
                </div>
            </div>
        </div>
    </div>

    @if($failedInvoices->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h6 class="mb-0">فواتير فاشلة تحتاج إعادة محاولة</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>النوع</th>
                        <th>الخطأ</th>
                        <th>المحاولات</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failedInvoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number ?? '-' }}</td>
                        <td>{{ $invoice->customer?->name ?? '-' }}</td>
                        <td>{{ $invoice->zatca_invoice_type_label }}</td>
                        <td class="text-danger" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">{{ $invoice->zatca_last_error }}</td>
                        <td>{{ $invoice->zatca_retry_count }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" disabled>إعادة</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($pendingInvoices->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h6 class="mb-0">فواتير معلقة</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>النوع</th>
                        <th>الحالة</th>
                        <th>تاريخ الإصدار</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingInvoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number ?? '-' }}</td>
                        <td>{{ $invoice->customer?->name ?? '-' }}</td>
                        <td>{{ $invoice->zatca_invoice_type_label }}</td>
                        <td>{{ $invoice->zatca_status_label }}</td>
                        <td>{{ $invoice->zatca_issued_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-success" disabled>إرسال</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">آخر الفواتير المرسلة بنجاح</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>النوع</th>
                        <th>الحالة</th>
                        <th>تاريخ الإرسال</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentInvoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number ?? '-' }}</td>
                        <td>{{ $invoice->customer?->name ?? '-' }}</td>
                        <td>{{ $invoice->zatca_invoice_type_label }}</td>
                        <td><span class="badge bg-success">{{ $invoice->zatca_status_label }}</span></td>
                        <td>{{ ($invoice->zatca_cleared_at ?? $invoice->zatca_reported_at)?->format('Y-m-d H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
