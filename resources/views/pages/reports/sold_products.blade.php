<!-- resources/views/categories/sold_products.blade.php -->
@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endsection

@section('content')
    <div class="filter-container">
        <form id="filterForm">
            <label for="startDate">Start Date:</label>
            <input type="date" id="startDate" name="startDate">
            <label for="endDate">End Date:</label>
            <input type="date" id="endDate" name="endDate">
            <label for="chartType">Chart Type:</label>
            <select id="chartType" name="chartType">
                <option value="bar">Bar</option>
                <option value="line">Line</option>
                <option value="pie">Pie</option>
            </select>
            <button type="submit">Update Chart</button>
        </form>
    </div>
    <div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>
    <script src="{{ asset('js/chart.js') }}"></script>
    <script>
        document.getElementById('filterForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const chartType = document.getElementById('chartType').value;

            // Fetch data based on selected dates and update the chart
            fetch(`/api/sales-report?start=${startDate}&end=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    updateChart(data, chartType);
                });
        });

        function updateChart(data, chartType) {
            const labels = data.map(item => item.date);
            const dataValues = data.map(item => item.total_qty);

            const ctx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Sold Quantity',
                        data: dataValues,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
@endsection
