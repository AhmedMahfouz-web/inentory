@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />

    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap.min.css') }}" />
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4 d-none" id="table_title">قائمة الاصناف</h4>
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5">
                    <div id="table_header" class="card-header flex-column flex-md-row">
                        <div class="head-label text-center">
                            <h5 class="card-title mb-0">قائمة الاصناف</h5>
                        </div>
                        <div class="dt-action-buttons d-flex flex-row-reverse text-end pt-3 pt-md-0" id="btn_container">
                            <div class="dt-buttons btn-group flex-wrap">
                                <button class="btn btn-secondary create-new btn-primary" tabindex="0"
                                    aria-controls="DataTables_Table_0" id="add_new_record_btn" type="button">
                                    <span><i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">تعريف
                                            صنف جديد</span></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label">بحث :</label>
                        <input type="search" class="form-control" id="search_input" placeholder=""
                            aria-controls="DataTables_Table_0">
                    </div>
                    <table id="data_table" class="datatables-basic display table nowrap table-hover">
                        <thead>
                            <tr>
                                <th>الكود</th>
                                <th>الاسم</th>
                                <th>التصنيف</th>
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
                                <tr {{ $product->stock < $product->min_stock ? 'class=alert-danger' : '' }}>
                                    <td>{{ $product->code }}</td>
                                    <td>
                                        <a href="{{ route('edit product', $product->id) }}">{{ $product->name }} </a>
                                    </td>
                                    <td>
                                        {{ $product->sub_category->name }}
                                    </td>
                                    <td>
                                        {{ $product->sub_category->category->name }}
                                    </td>
                                    <td>
                                        {{ $product->unit->name }}
                                    </td>
                                    <td>
                                        {{ $product->stock }}
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
                action="{{ route('store product') }}">
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
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">التصنيف</label>
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
    <script>
        let backdrop = document.getElementById('backdrop');
        let new_record = document.getElementById('add_new_record');
        let new_record_btn = document.getElementById('add_new_record_btn');
        let close_btn = document.getElementById('close');

        let close = function() {
            new_record.classList.remove('show');
            backdrop.classList.remove('offcanvas-backdrop');
            backdrop.classList.remove('fade');
            backdrop.classList.remove('show');
        };

        new_record_btn.addEventListener('click', function() {
            backdrop.classList.add('offcanvas-backdrop');
            backdrop.classList.add('fade');
            backdrop.classList.add('show');

            new_record.classList.add('show');
        });

        backdrop.addEventListener('click', close);
        close_btn.addEventListener('click', close);
    </script>
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
    <script src="{{ asset('vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        let table = $("#data_table").DataTable({
            "searching": false,
            scrollX: true,
            "language": {
                "emptyTable": "لم يضاف اي صنف الي هذا المخزن بعد"
            },
            order: [
                [0, 'asc']
            ],
            pagingType: 'simple_numbers',
            "iDisplayLength": 100,
            className: 'nowrap',
            dom: 'Bftip',
            buttons: [{
                extend: 'collection',
                text: '<i class="tf-icons ti ti-file-export me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                className: 'btn-label-primary me-2',


                buttons: [{
                        extend: 'excelHtml5',
                        // className: "new-success waves-effect",
                        text: '<i class="ti ti-file-spreadsheet"></i> Excel',
                        title: 'اصناف المخزن'
                    },
                    {
                        extend: 'pdfHtml5',
                        // className: "new-danger waves-effect",
                        text: '<i class="ti ti-file-type-pdf"></i> PDF',
                        title: 'اصناف المخزن'
                    }
                ],
            }]

        });
        table.buttons().container()
            .appendTo('#btn_container');
        // document.ready(function() {
        //     $("#data_table").addClass('table-hover');
        // });
        let search = document.getElementById("search_input");
        search.addEventListener('keyup', search_on_table);

        function search_on_table() {
            // Declare variables
            let input, table, tr, td, i, txtValue;
            input = this;
            filter = input.value
            table = document.getElementById("data_table");
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
@endsection
