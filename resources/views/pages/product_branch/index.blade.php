@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/jquery-timepicker/jquery-timepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/pickr/pickr-themes.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap.min.css') }}" />
    <style>
        .new-danger {
            background: #f06767;
            border-color: #f06767;
            margin-right: 1px !important;
            border-top-right-radius: inherit !important;
            border-bottom-right-radius: inherit !important;
            border-right-color: #f06767 !important;
            box-shadow: 0px 2px 4px rgba(174, 163, 163, 0.4);
        }

        .new-danger:hover {
            background: #d45b5b !important
        }

        .new-success {
            background: #67f079;
            border-color: #67f079;
            margin-left: 1px !important;
            border-top-left-radius: inherit !important;
            border-bottom-left-radius: inherit !important;
            border-left-color: #67f079 !important;
            box-shadow: 0px 2px 4px rgba(163, 174, 166, 0.4);
        }

        .new-success:hover {
            background: #59ce69 !important
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4 d-none" id="table_title">قائمة الاصناف</h4>
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                    <div id="table_header" class="card-header flex-column flex-md-row">
                        <div class="head-label text-center">
                            <h5 class="card-title mb-0">قائمة الاصناف</h5>
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
                                    <span><i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">تعريف
                                            صنف جديد</span></span>
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
                                        aria-controls="DataTables_Table_0"></label></div>
                        </div>
                    </div>
                    <button id="add_btn" type="button" class="d-none">تعريف صنف جديدة</button>
                    <table id="table" class="datatables-basic table">
                        <thead>
                            <tr>
                                <th>الكود</th>
                                <th>الاسم</th>
                                <th>القسم</th>
                                <th>الوحدة</th>
                                <th>الكمية</th>
                                <th>السعر</th>
                                <th>اقصي كمية</th>
                                <th>اقل كمية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>{{ $product->product->code }}</td>
                                    <td>
                                        {{ $product->name }}
                                    </td>
                                    <td>
                                        {{ $product->product->category->name }}
                                    </td>
                                    <td>
                                        {{ $product->product->unit->name }}
                                    </td>
                                    <td>
                                        {{ $product->qty }}
                                    </td>
                                    <td>
                                        {{ $product->price }}
                                    </td>
                                    <td>
                                        {{ $product->max_stock }}
                                    </td>
                                    <td>
                                        {{ $product->min_stock }}
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
            <h5 class="offcanvas-title" id="exampleModalLabel">تعريف صنف جديد</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" method="post"
                action="{{ route('store inventory', $branch->id) }}">
                @csrf
                <div class="col-sm-12 ">
                    <label class="mb-2 text-light fw-semibold" for="basicFullname">اسم الصنف</label>
                    <div class="input-group input-group-merge">
                        <span id="basicFullname2" class="input-group-text"><i class="ti ti-box"></i></span>
                        <input type="text" id="basicFullname" class="form-control dt-full-name" name="name"
                            placeholder="اسم الصنف" aria-label="اسم الصنف" aria-describedby="اسم الصنف" />
                    </div>
                </div>
                <div class="col-sm-12 mt-3">
                    <label class="mb-2 text-light fw-semibold" for="basicFullname">كود الصنف</label>
                    <div class="input-group input-group-merge">
                        <span id="basicFullname2" class="input-group-text"><i class="ti ti-id"></i></span>
                        <input type="text" id="basicFullname" class="form-control dt-full-name" name="code"
                            placeholder="الكود" aria-label="الكود" aria-describedby="الكود" />
                    </div>
                </div>
                <div class="col-sm-12 mt-3">
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">القسم</label>
                    <select class="select2 form-select form-select" name="category" data-allow-clear="true">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 mt-3">
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">الوحدة</label>
                    <select class="select2 select2Basic form-select form-select" name="unit" data-allow-clear="true">
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 mt-3">
                    <label class="mb-2 text-light fw-semibold" for="basicFullname">اقل كمية</label>
                    <div class="input-group input-group-merge">
                        <span id="basicFullname2" class="input-group-text"><i class="ti ti-arrows-minimize"></i></span>
                        <input type="text" id="basicFullname" class="form-control dt-full-name" name="min_stock"
                            placeholder="اقل كمية" aria-label="اقل كمية" aria-describedby="اقل كمية" />
                    </div>
                </div>
                <div class="col-sm-12 mt-3">
                    <label class="mb-2 text-light fw-semibold" for="basicFullname">اقصي كمية</label>
                    <div class="input-group input-group-merge">
                        <span id="basicFullname2" class="input-group-text"><i class="ti ti-arrows-maximize"></i></span>
                        <input type="text" id="basicFullname" class="form-control dt-full-name" name="max_stock"
                            placeholder="اقصي كمية" aria-label="اقصي كمية" aria-describedby="اقصي كمية" />
                    </div>
                </div>
                <div class="col-sm-12 mt-5">
                    <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">حفظ</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">الغاء</button>
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

        new_record_btn.addEventListener('click', function() {
            backdrop.classList.add('offcanvas-backdrop');
            backdrop.classList.add('fade');
            backdrop.classList.add('show');

            new_record.classList.add('show');
        });

        backdrop.addEventListener('click', function() {
            new_record.classList.remove('show');
            backdrop.classList.remove('offcanvas-backdrop');
            backdrop.classList.remove('fade');
            backdrop.classList.remove('show');
        });

        let search = document.getElementById("search_inout");
        search.addEventListener('keyup', search_on_table);

        function search_on_table() {
            // Declare variables
            let input, table, tr, name, category, i, txtValue;
            input = document.getElementById("search_inout");
            filter = input.value
            table = document.getElementById("table");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                name = tr[i].getElementsByTagName("td")[1];
                category = tr[i].getElementsByTagName("td")[2];
                if (name || category) {
                    txtValue = name.textContent || name.innerText;
                    categoryValue = category.textContent || category.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1 || categoryValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
    {{-- <script src="{{ asset('js/tables-datatables-basic.js') }}"></script> --}}
    <script src="{{ asset('js/forms-selects.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>


    <script src="{{ asset('vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/jquery-timepicker/jquery-timepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/pickr/pickr.js') }}"></script>

    <script src="{{ asset('js/forms-pickers.js') }}"></script>
    {{-- <script src="{{ asset('js/tables-datatables-basic.js') }}"></script> --}}
    <script src="{{ asset('js/forms-selects.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap.js') }}"></script>
    <script src="{{ asset('js/dataTables.dataTables.js') }}"></script>
    <script>
        let table = $("#data_table").DataTable({
            "searching": false,
            "language": {
                "emptyTable": "اختر التاريخ اولا."
            },
            order: [
                [0, 'desc']
            ],
            "iDisplayLength": 25,
            @if (!empty($start_date))

                buttons: [{
                        extend: 'excelHtml5',
                        className: "new-success waves-effect",
                        text: '<i class="ti ti-file-spreadsheet"></i> Excel',
                        title: 'الاضافات من {{ $start_date }} حتي {{ $end_date }}'
                    },
                    {
                        extend: 'pdfHtml5',
                        className: "new-danger waves-effect",
                        text: '<i class="ti ti-file-type-pdf"></i> PDF',
                        title: 'الاضافات من {{ $start_date }} حتي {{ $end_date }}'
                    }
                ]
            @endif

        });
        @if (!empty($start_date))
            table.buttons().container()
                .appendTo('#table_header');
        @endif

        $document.ready(function() {
            $("#data_table").addClass('table-hover');
        });
        let search = document.getElementById("search_input");
        search.addEventListener('keyup', search_on_table);

        function search_on_table() {
            // Declare variables
            let input, table, tr, td, i, txtValue;
            input = document.getElementById("search_input");
            filter = input.value
            table = document.getElementById("table");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[2];
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
@endsection
