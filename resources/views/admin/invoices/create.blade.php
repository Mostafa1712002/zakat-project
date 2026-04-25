@extends('layouts.app')

@section('title', 'فاتورة جديدة')

@section('content')
<div class="page-header">
    <h1>🧾 فاتورة جديدة</h1>
</div>

<form method="POST" action="{{ route('admin.invoices.store') }}">
    @csrf
    @include('admin.invoices._form', ['invoice' => null])
</form>
@endsection
