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
        <h4 class="fw-bold py-3 mb-4 d-none" id="table_title">قائمة التصنيفات</h4>
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                    <div class="card-header flex-column flex-md-row">
                        <div class="head-label text-center">
                            <h5 class="card-title mb-0">قائمة التصنيفات</h5>
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
                                            تصنيف جديدة</span></span>
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
                    <button id="add_btn" type="button" class="d-none">اضافة تصنيف جديدة</button>
                    <table id="table" class="datatables-basic table">
                        <thead>
                            <tr>
                                <th>الكود</th>
                                <th>الاسم</th>
                                <th>القسم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td>
                                        {{ $category->code }}
                                    </td>
                                    <td>
                                        <a href="{{ route('edit sub_category', $category->id) }}">{{ $category->name }} </a>
                                    </td>
                                    <td>
                                        {{ $category->category->name }} </a>
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
            <h5 class="offcanvas-title" id="exampleModalLabel">اضافة قسم جديدة</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" method="post"
                action="{{ route('store sub_category') }}">
                @csrf
                <div class="col-sm-12">
                    <label class="form-label" for="basicFullname">اسم التصنيف</label>
                    <div class="input-group input-group-merge">
                        <span id="basicFullname2" class="input-group-text"><i class="ti ti-category-2"></i></span>
                        <input type="text" id="basicFullname" class="form-control dt-full-name" name="name"
                            placeholder="اسم التصنيف" aria-label="اسم التصنيف" aria-describedby="اسم التصنيف" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="basicFullname">كود التصنيف</label>
                    <div class="input-group input-group-merge">
                        <span id="basicFullname2" class="input-group-text"><i class="ti ti-id"></i></span>
                        <input type="text" id="basicFullname" class="form-control dt-full-name" name="code"
                            placeholder="الكود" aria-label="الكود" aria-describedby="الكود" />
                    </div>
                </div>
                <div class="col-sm-12 mt-3">
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">القسم</label>
                    <select class="select2 form-select form-select" name="category" data-allow-clear="true">
                        @foreach ($parent_category as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
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
    <script src="{{ asset('js/forms-selects.js') }}"></script>

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
