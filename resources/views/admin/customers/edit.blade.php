@extends('layouts.app')

@section('title', 'تعديل عميل')

@section('content')
<div class="page-header">
    <div>
        <h1>✏️ تعديل عميل: {{ $customer->name }}</h1>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn">← رجوع</a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-right:1rem">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.customers.update', $customer) }}">
    @csrf
    @method('PUT')
    @include('admin.customers._form', ['customer' => $customer])

    <div style="display:flex;gap:.5rem">
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn">إلغاء</a>
    </div>
</form>
@endsection
