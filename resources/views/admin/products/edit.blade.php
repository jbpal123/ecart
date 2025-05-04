<!-- resources/views/admin/products/edit.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Product</h5>
    </div>
    <div class="card-body">
        @include('admin.products._form')
    </div>
</div>
@endsection