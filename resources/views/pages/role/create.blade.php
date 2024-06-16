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
    <div class="card mb-4">
        <h5 class="card-header">اضافة وظيفة جديدة</h5>
        <form class="card-body" method="post" action="{{ route('store role') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">اسم الوظيفة</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="اسم الوظيفة" />
                </div>
                <div class="col-12 mt-5">
                    <h6 class="" for="">الصلاحيات :</h6>
                    @foreach ($permissions as $permission)
                        <div class="col-md-2 form-check form-check-inline mx-5 my-2">
                            <input class="form-check-input" name="permissions[{{ $permission->id }}]" type="checkbox"
                                id="permission{{ $permission->id }}" value="{{ $permission->name }}" />
                            <label class="form-check-label"
                                for="permission{{ $permission->id }}">{{ __('permissions.' . $permission->name) }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-5 mb-3">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">حفظ</button>
                <a href="{{ route('show roles') }}" class="btn btn-label-secondary">الغاء</a>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script src="{{ asset('vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('vendor/libs/cleavejs/cleave-phone.js') }}"></script>
    <script src="{{ asset('vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>

    <script src="{{ asset('js/app-invoice-add.js') }}"></script>

    {{-- <script src="{{ asset('js/tables-datatables-basic.js') }}"></script> --}}
@endsection
