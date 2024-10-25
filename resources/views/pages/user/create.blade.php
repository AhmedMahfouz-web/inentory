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
        <h5 class="card-header">اضافة وحدة جديدة</h5>
        <form class="card-body" method="post" action="{{ route('store user') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">الاسم</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="الاسم" />
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="اسم المستخدم" />
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">البريد الالكترونى</label>
                    <input type="email" id="email" name="email" class="form-control"
                        placeholder="البريد الالكترونى" />
                </div>
                <div class="col-md-6 ">
                    <label class="form-label">الوظيفة :</label>

                    <select class=" select2Basic select2 form-select form-select-lg" data-allow-clear="true" name="role">
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="كلمة المرور" />
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">تاكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                        placeholder="تاكيد كلمة المرور" />
                </div>
            </div>
            <div class="mt-5">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">حفظ</button>
                <a href="{{ route('show users') }}" class="btn btn-label-secondary">الغاء</a>
            </div>
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
