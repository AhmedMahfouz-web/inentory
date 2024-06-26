<!DOCTYPE html>

<html lang="en" dir="rtl">

<head>
    <meta charset="utf-8" />
    <title>اذن تحويل</title>
    {{-- <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@100..900&display=swap" rel="stylesheet"> --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Readex+Pro:wght@160..700&display=swap');

        .readex-pro-<uniquifier> {
            font-family: "Readex Pro", sans-serif;
            font-optical-sizing: auto;
            font-weight: <weight>;
            font-style: normal;
            font-variation-settings:
                "HEXP" 0;
        }

        * {
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Readex Pro", sans-serif;
            direction: rtl;
            text-align: right;
        }

        .header,
        .footer {
            width: 100%;
            text-align: center;
            position: fixed;
        }

        .header {
            top: 0;
            padding: 0
        }

        .footer {
            bottom: 0;
            display: flex;
            flex-direction: column;
        }

        .footer div {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .footer div p {
            margin: 10px auto
        }

        .header h1 {
            font-size: 30px
        }

        .header h1 span {
            margin: 5px 0;
            display: block;
            font-size: 22px;
            color: red
        }

        .date {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-content: start;
            justify-content: start;
            margin-bottom: 25px
        }

        .date p {
            font-weight: bold;
            margin: 5px;
            align-self: flex-start;
        }

        .content {
            /* margin-top: 61mm; */
            /* Adjust according to your header height */
            margin-bottom: 30mm;
            /* Adjust according to your footer height */
            page-break-before: auto;
            width: 100%;
        }


        .divTable {
            display: table;
            width: 100%;
            font-size: 14px;
        }

        .content .divTable {
            position: relative;
            width: 99.9%;
            top: 61mm;
        }

        .divTableRow:first-child {
            border-top: 2px solid #202020;
        }

        .divTableRow:last-child {
            border-bottom: 2px solid #202020;
        }

        .divTableRow {
            /* display: table-row; */
            border-right: 2px solid #202020;
            border-left: 2px solid #202020;
        }

        .divTableHeading {
            background-color: #EEE;
            /* display: table-header-group; */
            width: 100%;
        }

        .divTableCell,
        .divTableHead {
            text-align: center;
            border: 1px solid #202020;
            display: table-cell;
            padding: 6px 5px;
            box-sizing: border-box;
            width: 80px;
        }

        .divTableHead:last-child,
        .divTableCell:last-child {
            width: 155px
        }

        .divTableHead:first-child,
        .divTableCell:first-child {
            max-width: 35px !important;
            box-sizing: border-box;
            width: 35px !important;
        }

        .divTableHead:nth-child(2),
        .divTableCell:nth-child(2) {
            max-width: 300px !important;
            width: 290px !important;
            box-sizing: border-box;
        }

        .divTableHead {
            /* border: 2px solid #202020; */
        }

        .divTableHeading {
            background-color: #EEE;
            /* display: table-header-group; */
            font-weight: bold;
        }

        .divTableFoot {
            background-color: #EEE;
            /* display: table-footer-group; */
            font-weight: bold;
        }

        .divTableBody {
            /* display: table-row-group; */
        }

        .page-break {
            page-break-after: always;
            margin-bottom: 182mm
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>اذن صرف مخزن <span>{{ $order->id }}</span></h1>
        <div class="date">
            <p>التاريخ : {{ date('Y-m-d', strtotime($order->created_at)) }}</p>
            <p>الفرع : {{ $order->branch->name }}</p>
        </div>
        <div class="divTable">
            <div class="divTableHeading">
                <div class="divTableRow">
                    <div class="divTableHead">م</div>
                    <div class="divTableHead">اسم الصنف</div>
                    <div class="divTableHead">الوحدة</div>
                    <div class="divTableHead">الكمية</div>
                    <div class="divTableHead">الملاحظات</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>
            <p>المستلم</p>
            <p>امين المخزن</p>
            <p>الحسابات</p>
        </div>
        <div>
            <p>.................................</p>
            <p>.................................</p>
            <p>.................................</p>
        </div>
    </div>


    <div class="content">
        {{-- <table>
            <thead>
                <tr>
                    <th>م</th>
                    <th> اسم الصنف </th>
                    <th>الوحدة</th>
                    <th>الكمية</th>
                    <th>الملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->product_added as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->product->name }}</td>
                        <td>{{ $product->product->unit->name }}</td>
                        <td>{{ $product->qty }}</td>
                        <td></td>
                    </tr>
                    @if ($index > 19)
                        <div class="page-break"></div>
                    @endif
                @endforeach
                @foreach ($order->product_added as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->product->name }}</td>
                        <td>{{ $product->product->unit->name }}</td>
                        <td>{{ $product->qty }}</td>
                        <td></td>
                    </tr>
                    @if ($index > 4)
                        <div class="page-break"></div>
                    @endif
                @endforeach
            </tbody>
        </table> --}}

        <div class="divTable">
            <div class="divTableBody">
                @foreach ($order->product_added as $index => $product)
                    <div class="divTableRow">
                        <div class="divTableCell">{{ $index + 1 }}</div>
                        <div class="divTableCell">{{ $product->product->name }}</div>
                        <div class="divTableCell">{{ $product->product->unit->name }}</div>
                        <div class="divTableCell">{{ $product->qty }}</div>
                        <div class="divTableCell"></div>
                    </div>
                    @if ($index == 19 || $index == 39)
            </div>
        </div>
        <div class="page-break"></div>
        <div class="divTable">
            <div class="divTableBody">
                @endif
                @endforeach
            </div>
        </div>

    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
