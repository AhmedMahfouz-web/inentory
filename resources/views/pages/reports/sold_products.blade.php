<!-- resources/views/categories/sold_products.blade.php -->
@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endsection

@section('content')
    <div class="col-12">
        <h1>تقرير المنتجات المباعة</h1>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h2>الفئة: {{ $category->name }}</h2>
        <h3>التاريخ: {{ $date }}</h3>
        <p>إجمالي الكمية المباعة: {{ $sold_quantity }}</p>

        <a href="{{ url()->previous() }}" class="btn btn-primary">عودة</a>
    </div>
@endsection

@section('js')
    <script src="{{ asset('vendor/libs/datatables-bs5/datatables.bootstrap5.js') }}"></script>
    <script>
        // You can add any specific JavaScript for this page here
    </script>
@endsection
