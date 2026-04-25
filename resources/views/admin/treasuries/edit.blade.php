@extends('layouts.app')
@section('title', 'تعديل ' . $treasury->name)
@section('content')
<div class="page-header"><h1>✏️ تعديل {{ $treasury->name }}</h1></div>
<form method="POST" action="{{ route('admin.treasuries.update', $treasury) }}" class="card form-card">
    @csrf @method('PUT')
    @include('admin.treasuries._form')
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 حفظ</button>
        <a href="{{ route('admin.treasuries.index') }}" class="btn btn-secondary">إلغاء</a>
    </div>
</form>
@endsection
