@extends('layouts.dashboard')

@section('css')
    <style>
        .btn-outline-primary:hover {
            background: #7367f0 !important;
            color: #f4f3fe !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">الوصول السريع</h5>
                <div class="card-body">
                    <small class="text-light fw-semibold">المخازن</small>
                    <div class="demo-inline-spacing">
                        <a href="{{ route('product inventory') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-database"></i> مخزن رئيسي</a>
                        @foreach ($branches as $branch)
                            <a href="{{ route('inventory', $branch->id) }}" class="btn rounded-pill btn-outline-primary"><i
                                    class="menu-icon tf-icons ti ti-database"></i> {{ $branch->name }}</a>
                        @endforeach
                    </div>
                </div>
                <hr class="m-0" />
                <div class="card-body">
                    <small class="text-light fw-semibold">الحركات</small>
                    <div class="demo-inline-spacing">
                        <a href="{{ route('show order') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-clipboard-text"></i> اذون الصرف</a>
                        <a href="{{ route('create exchange product') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-transfer-out"></i> تحويل</a>
                        <a href="{{ route('exchanged product') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-transfer-out"></i> عرض التحويلات</a>
                        <a href="{{ route('create increase product') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-transfer-in"></i> اضافة</a>
                        <a href="{{ route('increased product') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-transfer-in"></i> عرض الاضافات</a>
                    </div>
                </div>
                <hr class="m-0" />
                <div class="card-body">
                    <small class="text-light fw-semibold">الاعدادات</small>
                    <div class="demo-inline-spacing">
                        <a href="{{ route('show suppliers') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-truck-delivery"></i> الموردون</a>
                        <a href="{{ route('show branches') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-building-warehouse"></i> المخازن الفرعية</a>
                        <a href="{{ route('show products') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon ti ti-box"></i> الاصناف</a>
                        <a href="{{ route('show categories') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon ti ti-category"></i> الاقسام</a>
                        <a href="{{ route('show sub_categories') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon ti ti-category-2"></i> التصنيفات</a>
                        <a href="{{ route('show units') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon ti ti-weight"></i> الوحدات</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-danger mb-2">
                        <i class="menu-icon tf-icons ti ti-shopping-cart ti-sm mx-0"></i>
                    </div>
                    <h5 class="card-title mb-2">
                        الوارد
                    </h5>
                    <h6>{{ number_format($total_income, 2, '.', ',') }}ج</h6>
                    <p class="text-light fw-semibold mb-1">مخزن رئيسي</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-success mb-2">
                        <i class="menu-icon tf-icons ti ti-currency-dollar ti-sm mx-0"></i>
                    </div>
                    <h5 class="card-title mb-2">
                        الصادر
                    </h5>
                    <h6>{{ number_format($total_sells, 2, '.', ',') }}ج</h6>
                    <p class="text-light fw-semibold mb-1">مخزن رئيسي</p>
                </div>
            </div>
        </div>
    </div>
@endsection
