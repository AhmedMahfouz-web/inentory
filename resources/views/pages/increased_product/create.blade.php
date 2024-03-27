@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />

    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />

    <link rel="stylesheet" href="{{ asset('vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/jquery-timepicker/jquery-timepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/pickr/pickr-themes.css') }}" />
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row invoice-add position-relative">
            <!-- Invoice Add-->
            <div class="col-lg-9 p-0 col-12 mb-lg-0 mb-4 ">
                <div class="card invoice-preview-card">

                    <div class="card-body pe-3 px-3">
                        <h5 class="card-title">اضافة اصناف للمخزون</h5>
                        <form id="create"action="{{ route('increase product') }}" method="post">

                            <div class="source-item p-0">
                                @csrf
                                <div class="mb-3" data-repeater-list="product">
                                    <div class="repeater-wrapper pt-0 pe-0  pt-md-4" data-repeater-item>
                                        <div class="d-flex border rounded position-relative pe-0">
                                            <div class="row w-100 p-3 pe-0">
                                                <div class="col-md-5 col-12 pe-0 mb-md-0 mb-3">
                                                    <p class="mb-2 repeater-title">الصنف</p>
                                                    <select class="select2 select2Basic form-select form-select-lg"
                                                        data-allow-clear="true" name="product_id">
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}">
                                                                {{ $product->code . ' - ' . $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                                <div class="col-md-2 col-12 pe-0 mb-md-0 mb-3">
                                                    <p class="mb-2 repeater-title">الكمية</p>
                                                    <input type="number" class="form-control invoice-item-qty"
                                                        placeholder="1" min="1" name="qty" />
                                                </div>
                                                <div class="col-md-2 col-12 pe-0 mb-md-0 mb-3">
                                                    <p class="mb-2 repeater-title">سعر الوحدة</p>
                                                    <input type="decimal" class="form-control invoice-item-qty"
                                                        placeholder="1.00" min="1" name="price" />
                                                </div>

                                                <div class="col-md-3 col-12 mb-md-0 pe-0 mb-3">
                                                    <p class="mb-2 repeater-title">المورد</p>
                                                    <select class="select2 select2Basic form-select form-select-lg"
                                                        data-allow-clear="true" name="supplier_id">
                                                        @foreach ($suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}">
                                                                {{ $supplier->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row pb-4">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary" id='data_repeater'
                                            data-repeater-create>اضافة صنف</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Invoice Add-->

            <!-- Invoice Actions -->
            <div class="col-lg-3 col-12 invoice-actions ">
                <div class="card mb-4">
                    <div class="card-body">
                        <button form="create" class="btn btn-primary d-grid w-100 mb-2">
                            <span class="d-flex align-items-center justify-content-center text-nowrap">حفظ</span>
                        </button>
                        <a href="{{ route('increased product') }}"
                            class="btn btn-label-secondary d-grid w-100 mb-2">الغاء</a>
                    </div>
                </div>
            </div>
            <!-- /Invoice Actions -->
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('vendor/libs/cleavejs/cleave-phone.js') }}"></script>
    <script src="{{ asset('vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>

    <script src="{{ asset('js/app-invoice-add.js') }}"></script>

    {{-- <script src="{{ asset('js/tables-datatables-basic.js') }}"></script> --}}
@endsection
