@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />

    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4 d-none" id="table_title">قائمة الموردين</h4>
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                    <div class="card-header flex-column flex-md-row">
                        <div class="head-label text-center">
                            <h5 class="card-title mb-0">قائمة الموردين</h5>
                        </div>
                        <div class="dt-action-buttons text-end pt-3 pt-md-0">
                            <div class="dt-buttons btn-group flex-wrap">
                                <div class="btn-group">
                                    <button
                                        class="btn btn-secondary buttons-collection dropdown-toggle btn-label-primary me-2"
                                        tabindex="0" aria-controls="DataTables_Table_0" type="button"
                                        aria-haspopup="dialog" aria-expanded="false">
                                        <span><i class="ti ti-file-export me-sm-1"></i> <span
                                                class="d-none d-sm-inline-block">Export</span></span><span
                                            class="dt-down-arrow"></span>
                                    </button>
                                </div>
                                <button class="btn btn-secondary create-new btn-primary" tabindex="0"
                                    aria-controls="DataTables_Table_0" id="add_new_record_btn" type="button">
                                    <span><i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">اضافة
                                            مورد جديد</span></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length" id="DataTables_Table_0_length"><label>Show <select
                                        name="DataTables_Table_0_length" aria-controls="DataTables_Table_0"
                                        class="form-select form-select-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="75">75</option>
                                        <option value="100">100</option>
                                    </select> entries</label></div>
                        </div>
                        <div class="col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end">
                            <div id="DataTables_Table_0_filter" class="dataTables_filter"><label>Search:<input
                                        type="search" class="form-control form-control-sm" id="search_inout" placeholder=""
                                        aria-controls="DataTables_Table_0"></label>
                            </div>
                        </div>
                    </div>
                    <button id="add_btn" type="button" class="d-none">اضافة مورد جديد</button>
                    <table id="table" class="datatables-basic table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الهاتف</th>
                                <th>وصف</th>
                                <th>العنوان</th>
                                <th>السجل التجاري</th>
                                <th>البطاقة الضريبية</th>
                                <th>التوصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $supplier)
                                <tr>
                                    <td>{{ $supplier->id }}</td>
                                    <td>
                                        <a href="{{ route('edit supplier', $supplier->id) }}">{{ $supplier->name }} </a>
                                    </td>
                                    <td>{{ $supplier->phone }}</td>
                                    <td>{{ $supplier->desc }}</td>
                                    <td>{{ $supplier->address }}</td>
                                    <td>{{ $supplier->segel_togary }}</td>
                                    <td>{{ $supplier->betaqa_drebya }}</td>
                                    <td>
                                        @if ($supplier->has_delivery)
                                            نعم
                                        @else
                                            @if (!$supplier->has_delivery)
                                                لا
                                            @else
                                                <small class="text-light fw-semibold">لم يتم التحديد</small>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add_new_record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">اضافة مورد جديد</h5>
            <button type="button" class="btn-close cancel-btn text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">

            <form class="card-body add-new-record pt-0 row g-2" id="form-add-new-record" method="post"
                enctype="multipart/form-data" action="{{ route('store supplier') }}">
                @csrf
                <div class="col-sm-12">
                    <label class="mb-2 text-light fw-semibold" for="name">اسم المورد</label>
                    <input type="text" id="name" name="supplier_name" class="form-control mb-3"
                        placeholder="اسم المورد" />
                </div>
                <div class="col-sm-12">
                    <label class="mb-2 text-light fw-semibold" for="phone">الهاتف</label>
                    <input type="text" id="phone" name="phone" class="form-control mb-3" placeholder="الهاتف" />
                </div>
                <div class="col-sm-12">
                    <label class="mb-2 text-light fw-semibold" for="desc">الوصف</label>
                    <textarea type="text" id="desc" name="desc" class="form-control mb-3" placeholder="الوصف"></textarea>
                </div>
                <div class="col-sm-12">
                    <label class="mb-2 text-light fw-semibold" for="address">العنوان</label>
                    <input type="text" id="address" name="address" class="form-control mb-3"
                        placeholder="العنوان" />
                </div>
                <div class="col-sm-12">
                    <label class="mb-2 text-light fw-semibold" for="segel_togary">رقم السجل التجاري</label>
                    <input type="text" id="segel_togary" name="segel_togary" class="form-control mb-3"
                        placeholder="السجل التجاري" />
                </div>
                <div class="col-12">
                    <label for="file" class="text-light mb-2 fw-semibold">صورة السجل التجاري</label>
                    <div class="card">
                        <div class="card-body">
                            <div class="fallback mb-3">
                                <input name="segel_togary_image" type="file" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="mb-2 text-light fw-semibold" for="name">رقم البطاقة الضريبية</label>
                    <input type="text" id="betaqa_drebya" name="betaqa_drebya" class="form-control"
                        placeholder="رقم البطاقة الضريبية" />
                </div>
                <div class="col-sm-12">
                    <label for="file" class="text-light mb-2 fw-semibold">صورة البطاقة الضريبة</label>
                    <div class="card">
                        <div class="card-body">
                            <div class="fallback ">
                                <input name="betaqa_drebya_image" type="file" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 p-4">
                        <div class="text-light fw-semibold mb-2">التوصيل</div>
                        <label class="switch switch-lg">
                            <input type="checkbox" name="has_delivery" class="switch-input">
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
                <div class="col-sm-12 mt-3 d-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">حفظ</button>
                    <button type="reset" class="btn btn-outline-secondary cancel-btn"
                        data-bs-dismiss="offcanvas">الغاء</button>
                </div>
            </form>
        </div>
    </div>
    <div id="backdrop"></div>
@endsection

@section('js')
    <script src="{{ asset('vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        let backdrop = document.getElementById('backdrop');
        let new_record = document.getElementById('add_new_record');
        let new_record_btn = document.getElementById('add_new_record_btn');
        let cancel_btn = document.getElementsByClassName('cancel-btn');

        const hide = function() {
            new_record.classList.remove('show');
            backdrop.classList.remove('offcanvas-backdrop');
            backdrop.classList.remove('fade');
            backdrop.classList.remove('show');
        }

        new_record_btn.addEventListener('click', function() {
            backdrop.classList.add('offcanvas-backdrop');
            backdrop.classList.add('fade');
            backdrop.classList.add('show');

            new_record.classList.add('show');
        });

        backdrop.addEventListener('click', hide);
        for (let i = 0; i < cancel_btn.length; i++) {
            cancel_btn[i].addEventListener('click', hide);
        }


        let search = document.getElementById("search_inout");
        search.addEventListener('keyup', search_on_table);

        function search_on_table() {
            // Declare variables
            let input, table, tr, td, i, txtValue;
            input = document.getElementById("search_inout");
            filter = input.value
            table = document.getElementById("table");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
    {{-- <script src="{{ asset('js/tables-datatables-basic.js') }}"></script> --}}
@endsection
