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

                                <button class="btn btn-secondary create-new btn-primary" tabindex="0"
                                    aria-controls="DataTables_Table_0" id="add_new_record_btn" type="button">
                                    <span><i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">اضافة
                                            مستخدم جديدة</span></span>
                                </button>
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
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <a href="{{ route('edit user', $user->id) }}">{{ $user->name }} </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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

    {{-- <script src="{{ asset('js/tables-datatables-basic.js') }}"></script> --}}
@endsection
