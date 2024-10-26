<!-- resources/views/categories/index.blade.php -->
@extends('layouts.dashboard')

@section('content')
    <div class="col-12">
        <h1>تقرير المبيعات حسب الفئة</h1>

        <form method="GET" action="{{ route('report sold by category') }}">
            <label for="date">اختر التاريخ:</label>
            <input type="month" id="date" name="date" value="{{ $date }}">
            <button type="submit" class="btn btn-primary">عرض</button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>الفئة</th>
                    <th>إجمالي الكمية المباعة</th>
                    <th>إجمالي سعر البيع</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categoriesSummary as $summary)
                    <tr>
                        <td>{{ $summary['name'] }}</td>
                        <td>{{ $summary['total_sold'] }}</td>
                        <td>{{ number_format($summary['total_price'], 2) }} ر.س</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
