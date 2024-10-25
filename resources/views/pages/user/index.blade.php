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
        <h4 class="fw-bold py-3 mb-4 d-none" id="table_title">قائمة المستخدمين</h4>
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                    <div class="card-header flex-column flex-md-row">
                        <div class="head-label text-center">
                            <h5 class="card-title mb-0">قائمة المستخدمين</h5>
                        </div>
                        <div class="dt-action-buttons text-end pt-3 pt-md-0">
                            <div class="dt-buttons btn-group flex-wrap">
                                @can('user-create')
                                    <button class="btn btn-secondary create-new btn-primary" tabindex="0"
                                        aria-controls="DataTables_Table_0" id="add_new_record_btn" type="button">
                                        <span><i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">اضافة
                                                مستخدم جديدة</span></span>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
                <button id="add_btn" type="button" class="d-none">اضافة وحدة جديدة</button>
                <table id="data_table" class="datatables-basic table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                        </tr>
                    </thead>
                    <tbody>

                        @can('user-edit')
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <a href="{{ route('edit user', $user->id) }}">{{ $user->name }} </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endcan

                        @cannot('user-edit')
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    {{ $user->name }}
                                </td>
                            </tr>
                        @endcannot
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @can('user-create')
        <div class="offcanvas offcanvas-end" id="add_new_record">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="exampleModalLabel">اضافة مستخدم جديدة</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-1">
                <form class="add-new-record pt-0 row g-2" id="form-add-new-record" method="post"
                    action="{{ route('store user') }}">
                    @csrf
                    <div class="col-sm-12">
                        <label class="form-label" for="name">الاسم</label>
                        <div class="input-group input-group-merge">
                            <span id="basicFullname2" class="input-group-text"><i class="ti ti-user"></i></span>
                            <input type="text" id="name" class="form-control dt-full-name" name="name"
                                placeholder="الاسم" aria-label="الاسم" aria-describedby="الاسم" />
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <label class="form-label" for="username">اسم المستخدم</label>
                        <div class="input-group input-group-merge">
                            <span id="basicFullname2" class="input-group-text"><i class="ti ti-id"></i></span>
                            <input type="text" id="username" class="form-control dt-full-name" name="username"
                                placeholder="اسم المستخدم" aria-label="اسم المستخدم" aria-describedby="اسم المستخدم" />
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <label class="form-label" for="email">البريد الالكتروني</label>
                        <div class="input-group input-group-merge">
                            <span id="basicFullname2" class="input-group-text"><i class="ti ti-at"></i></span>
                            <input type="text" id="email" class="form-control dt-full-name" name="email"
                                placeholder="البريد الالكتروني" aria-label="البريد الالكتروني"
                                aria-describedby="البريد الالكتروني" />
                        </div>
                    </div>
                    <div class="col-sm-12 mt-3">
                        <label for="select2Basic" class="mb-2 text-light fw-semibold">الوظيفة</label>
                        <select class="select2 form-select form-select" name="role" data-allow-clear="true">
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-12">
                        <label class="form-label" for="password">كلمة المرور</label>
                        <div class="input-group input-group-merge">
                            <span id="basicFullname2" class="input-group-text"><i class="ti ti-brand-samsungpass"></i></span>
                            <input type="text" id="password" class="form-control dt-full-name" name="password"
                                placeholder="كلمة المرور" aria-label="كلمة المرور" aria-describedby="كلمة المرور" />
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <label class="form-label" for="confrim_password">تاكيد كلمة المرور</label>
                        <div class="input-group input-group-merge">
                            <span id="basicFullname2" class="input-group-text"><i class="ti ti-brand-samsungpass"></i></span>
                            <input type="text" id="confirm_password" class="form-control dt-full-name"
                                name="confirm_passwrod" placeholder="تاكيد كلمة المرور" aria-label="تاكيد كلمة المرور"
                                aria-describedby="تاكيد كلمة المرور" />
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
    @endcan
@endsection

@section('js')
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
                "emptyTable": "لم يضاف اي مستخدم الي هذا المخزن بعد"
            },
            order: [
                [0, 'asc']
            ],
            "iDisplayLength": 25,
            className: 'nowrap',


        });

        // table.buttons().container()
        //     .appendTo('#table_header');
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

    {{-- <script src="{{ asset('js/tables-datatables-basic.js') }}"></script> --}}
@endsection
