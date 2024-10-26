<!-- resources/views/categories/index.blade.php -->
@extends('layouts.dashboard')

@section('content')
    <div class="col-12">
        <h1>تقرير المبيعات حسب الفئة</h1>
        <h2>الفرع: {{ $branch->name }}</h2>
        <h3>التاريخ: {{ $date }}</h3>

        <form method="GET" action="{{ route('reports sold by category', $branch->id) }}">
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
                @foreach ($salesData as $data)
                    <tr>
                        <td>{{ $data['category_name'] }}</td>
                        <td>{{ $data['total_sold'] }}</td>
                        <td>{{ number_format($data['total_price'], 2) }} ر.س</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
