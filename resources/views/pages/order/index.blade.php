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
        <h4 class="fw-bold py-3 mb-4 d-none" id="table_title">قائمة اذون الصرف</h4>
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div id="DataTables_Table_0_wrapper" class="container dataTables_wrapper dt-bootstrap5 no-footer">
                    <div id="table_header" class="card-header flex-column flex-md-row">
                        <div class="head-label text-center">
                            <h5 class="card-title mb-0">قائمة اذون الصرف</h5>
                        </div>
                    </div>
                    <form class="d-flex " action="{{ route('show order date') }}" method="POST">
                        @csrf
                        <div class="col-sm-6 col-md-3">
                            <label for="flatpickr-date" class="form-label">البداية</label>
                            <input type="text" {{ !empty($start_date) ? 'value=' . $start_date : '' }} name="start_date"
                                class="form-control flatpickr-date" placeholder="YYYY-MM-DD" />
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label for="flatpickr-date" class="form-label">النهاية</label>
                            <input type="text"{{ !empty($end_date) ? 'value=' . $end_date : '' }} name="end_date"
                                class="form-control flatpickr-date" placeholder="YYYY-MM-DD" />
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <label class="form-label">بحث :</label>
                            <input type="search" class="form-control" id="search_input" placeholder=""
                                aria-controls="DataTables_Table_0">
                        </div>
                        <div class="col-sm-6 col-md-3 d-grid gap-2 mx-auto">
                            <button class="btn btn-primary btn-md mt-4">بحث</button>
                        </div>
                    </form>
                    <table id="data_table" class="datatables-basic table table-hover">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>رقم الاذن</th>
                                <th>الفرع</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($orders))
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ date('Y-m-d', strtotime($order->created_at)) }}</td>
                                        <td>{{ $order->id }}</td>
                                        <td>
                                            {{ $order->branch->name }}
                                        </td>
                                        <td>
                                            @can('order_print')
                                                <a href="{{ route('print', $order->id) }}" target="_blank"
                                                    class="btn btn-primary"><i class="ti ti-printer mx-1"></i> طباعة</a>
                                            @endcan
                                            @can('order_edit')
                                                <a href="{{ route('edit product_added', $order->id) }}"
                                                    class="btn btn-warning "><i class="ti ti-edit mx-1"></i> تعديل</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
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
                        title: 'اذون التحويل من {{ $start_date }} حتي {{ $end_date }}'
                    },
                    {
                        extend: 'pdfHtml5',
                        className: "new-danger waves-effect",
                        text: '<i class="ti ti-file-type-pdf"></i> PDF',
                        title: 'اذون التحويل من {{ $start_date }} حتي {{ $end_date }}'
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
