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
            <div class="col-lg-9 col-12 mb-lg-0 mb-4 ">
                <div class="card invoice-preview-card">

                    <div class="card-body">
                        <form id="create"action="{{ route('exchange product') }}" method="post">
                            <div class="row p-sm-4 p-0">
                                <div class="col-md-6 col-sm-5 col-12 mb-sm-0 mb-4">
                                    <h6 class="mb-4">الفرع :</h6>

                                    <div class="col-sm-12 col-md-6">
                                        <label for="flatpickr-date" class="form-label">التاريخ</label>
                                        <input type="text" name="created_at" form="create"
                                            class="form-control flatpickr-date" placeholder="YYYY-MM-DD" />
                                    </div>

                                    <select form="create" class=" select2Basic select2 form-select form-select-lg"
                                        data-allow-clear="true" name="branch_id">
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <hr class="my-3 mx-n4" />

                            <div class="source-item pt-2">
                                @csrf
                                <div class="mb-3" data-repeater-list="product">
                                    <div class="repeater-wrapper pt-0 pt-md-4" data-repeater-item>
                                        <div class="d-flex border rounded position-relative pe-0">
                                            <div class="row w-100 p-3">
                                                <div class="col-md-6 col-12 mb-md-0 mb-3">
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
                                                <div class="col-md-2 col-12 mb-md-0 mb-3">
                                                    <p class="mb-2 repeater-title">الكمية</p>
                                                    <input type="number" class="form-control invoice-item-qty"
                                                        placeholder="1" min="1" max="" name="qty" />
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
                        <a href="{{ route('exchanged product') }}"
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
    <script src="{{ asset('vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/jquery-timepicker/jquery-timepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/pickr/pickr.js') }}"></script>

    <script src="{{ asset('js/forms-pickers.js') }}"></script>

    <script src="{{ asset('js/app-invoice-add.js') }}"></script>

    {{-- <script src="{{ asset('js/tables-datatables-basic.js') }}"></script> --}}
@endsection
