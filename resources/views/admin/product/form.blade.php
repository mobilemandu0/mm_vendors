@extends('adminlte::page')

@section('title', isset($product) ? 'Edit Product: ' . $product->name : 'Create New Product')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">{{ isset($product) ? 'Edit Product' : 'Create New Product' }}</h1>
            @if (isset($product))
                <p class="text-muted mb-0">Product ID: #{{ $product->id }}</p>
            @endif
        </div>
        <a href="{{ isset($product) ? route('product.show', $product->id) : route('products') }}"
            class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if (isset($product))
            @livewire('product-form', ['product' => $product])
        @else
            @livewire('product-form')
        @endif
    </div>
@stop

@section('css')
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            border-radius: 0.25rem;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
@stop
