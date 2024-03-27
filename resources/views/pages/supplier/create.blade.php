@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/dropzone/dropzone.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/node-waves/node-waves.css') }}" />
@endsection

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">اضافة مورد جديد</h5>
        <form class="card-body" method="post" action="{{ route('store supplier') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="name">اسم المورد</label>
                    <input type="text" id="name" name="supplier_name" class="form-control"
                        placeholder="اسم المورد" />
                </div>
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="phone">الهاتف</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="الهاتف" />
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="desc">الوصف</label>
                    <textarea type="text" id="desc" name="desc" class="form-control" placeholder="الوصف"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="address">العنوان</label>
                    <input type="text" id="address" name="address" class="form-control" placeholder="العنوان" />
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="segel_togary">رقم السجل التجاري</label>
                    <input type="text" id="segel_togary" name="segel_togary" class="form-control"
                        placeholder="السجل التجاري" />
                </div>
                <div class="col-6">
                    <label for="file" class="text-light mb-2 fw-semibold">صورة السجل التجاري</label>
                    <div class="card">
                        <div class="card-body">
                            <div class="fallback ">
                                <input name="segel_togary_image" type="file" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="name">رقم البطاقة الضريبية</label>
                    <input type="text" id="betaqa_drebya" name="betaqa_drebya" class="form-control"
                        placeholder="رقم البطاقة الضريبية" />
                </div>
                <div class="col-md-6">
                    <label for="file" class="text-light mb-2 fw-semibold">صورة البطاقة الضريبة</label>
                    <div class="card">
                        <div class="card-body">
                            <div class="fallback ">
                                <input name="betaqa_drebya_image" type="file" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 p-4">
                    <div class="text-light fw-semibold mb-3">التوصيل</div>
                    <label class="switch switch-lg">
                        <input type="checkbox" name="has_delivery" value="1" class="switch-input">
                        <span class="switch-toggle-slider">
                            <span class="switch-on">
                                <i class="ti ti-check"></i>
                            </span>
                            <span class="switch-off">
                                <i class="ti ti-x"></i>
                            </span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="mt-5">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">حفظ</button>
                <a href="{{ route('show suppliers') }}" class="btn btn-label-secondary">الغاء</a>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script src="{{ asset('vendor/libs/dropzone/dropzone.js') }}"></script>
    <script src="{{ asset('js/forms-file-upload.js') }}"></script>
@endsection
