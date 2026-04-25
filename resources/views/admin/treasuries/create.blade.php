@extends('layouts.app')
@section('title', 'خزينة جديدة')
@section('content')
<div class="page-header"><h1>➕ خزينة جديدة</h1></div>
<form method="POST" action="{{ route('admin.treasuries.store') }}" class="card form-card">
    @csrf
    @include('admin.treasuries._form')
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 حفظ</button>
        <a href="{{ route('admin.treasuries.index') }}" class="btn btn-secondary">إلغاء</a>
    </div>
</form>
@endsection
