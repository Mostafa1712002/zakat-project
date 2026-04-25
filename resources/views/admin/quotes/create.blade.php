@extends('layouts.app')

@section('title', 'عرض سعر جديد')

@section('content')
<div class="page-header">
    <h1>📑 عرض سعر جديد</h1>
</div>

<form method="POST" action="{{ route('admin.quotes.store') }}">
    @csrf
    @include('admin.quotes._form', ['quote' => null])
</form>
@endsection
