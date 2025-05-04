<!-- resources/views/admin/products/create.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Add New Product</h5>
    </div>
    <div class="card-body">
        @include('admin.products._form')
    </div>
</div>
@endsection