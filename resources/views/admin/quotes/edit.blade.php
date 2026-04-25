@extends('layouts.app')

@section('title', 'تعديل عرض السعر')

@section('content')
<div class="page-header">
    <h1>✏️ تعديل عرض السعر — {{ $quote->quote_number }}</h1>
</div>

@if ($quote->status !== \App\Domain\Sales\Models\Quote::STATUS_DRAFT)
    <div class="alert alert-warning">
        لا يمكن تعديل عرض السعر بعد تقديمه (الحالة الحالية: {{ $quote->status }}).
    </div>
@else
    <form method="POST" action="{{ route('admin.quotes.update', $quote) }}">
        @csrf
        @method('PUT')
        @include('admin.quotes._form', ['quote' => $quote])
    </form>
@endif
@endsection
