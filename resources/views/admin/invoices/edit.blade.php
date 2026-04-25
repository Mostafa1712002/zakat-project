@extends('layouts.app')

@section('title', 'تعديل فاتورة — ' . $invoice->invoice_number)

@section('content')
<div class="page-header">
    <h1>🧾 تعديل الفاتورة — {{ $invoice->invoice_number }}</h1>
    <p>التعديل ممكن فقط للمسودات</p>
</div>

@if ($invoice->status !== \App\Domain\Sales\Models\Invoice::STATUS_DRAFT)
    <div class="alert alert-warning">
        لا يمكن تعديل الفاتورة بعد إصدارها (الحالة: {{ $invoice->status }}).
    </div>
@else
    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}">
        @csrf
        @method('PUT')
        @include('admin.invoices._form', ['invoice' => $invoice])
    </form>
@endif
@endsection
