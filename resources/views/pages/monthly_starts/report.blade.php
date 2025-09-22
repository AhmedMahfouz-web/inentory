@extends('layouts.dashboard')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">تقرير بداية الشهر - {{ $report['month'] }}</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ti ti-calendar"></i>
                            تغيير الشهر
                        </button>
                        <ul class="dropdown-menu">
                            @for($i = 0; $i < 12; $i++)
                                @php
                                    $monthOption = \Carbon\Carbon::now()->subMonths($i)->format('Y-m');
                                @endphp
                                <li>
                                    <a class="dropdown-item" href="{{ route('monthly starts report', ['month' => $monthOption]) }}">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $monthOption)->format('F Y') }}
                                    </a>
                                </li>
                            @endfor
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="ti ti-package display-6 mb-2"></i>
                                    <h4 class="mb-1">{{ $report['summary']['main_products_count'] }}</h4>
                                    <small>منتجات المخزن الرئيسي</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="ti ti-building-store display-6 mb-2"></i>
                                    <h4 class="mb-1">{{ $report['summary']['branch_products_count'] }}</h4>
                                    <small>منتجات الفروع</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="ti ti-sum display-6 mb-2"></i>
                                    <h4 class="mb-1">{{ number_format($report['summary']['total_main_qty']) }}</h4>
                                    <small>إجمالي كمية المخزن الرئيسي</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="ti ti-sum display-6 mb-2"></i>
                                    <h4 class="mb-1">{{ number_format($report['summary']['total_branch_qty']) }}</h4>
                                    <small>إجمالي كمية الفروع</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">تحليل الأقسام</h5>
                </div>
                <div class="card-body">
                    @if($report['category_analysis']['category_totals']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="categoryAnalysisTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>القسم</th>
                                        <th>عدد المنتجات (رئيسي)</th>
                                        <th>الكمية (رئيسي)</th>
                                        <th>القيمة (رئيسي)</th>
                                        <th>عدد المنتجات (فروع)</th>
                                        <th>الكمية (فروع)</th>
                                        <th>القيمة (فروع)</th>
                                        <th>إجمالي المنتجات</th>
                                        <th>إجمالي الكمية</th>
                                        <th>إجمالي القيمة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['category_analysis']['category_totals'] as $category)
                                        <tr>
                                            <td><strong>{{ $category['category_name'] }}</strong></td>
                                            <td><span class="badge bg-primary">{{ number_format($category['main_products_count']) }}</span></td>
                                            <td>{{ number_format($category['main_total_qty']) }}</td>
                                            <td>{{ number_format($category['main_total_value'], 2) }}</td>
                                            <td><span class="badge bg-info">{{ number_format($category['branch_products_count']) }}</span></td>
                                            <td>{{ number_format($category['branch_total_qty']) }}</td>
                                            <td>{{ number_format($category['branch_total_value'], 2) }}</td>
                                            <td><span class="badge bg-success">{{ number_format($category['total_products_count']) }}</span></td>
                                            <td><strong>{{ number_format($category['total_qty']) }}</strong></td>
                                            <td><strong>{{ number_format($category['total_value'], 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-category-off display-1 text-muted"></i>
                            <p class="text-muted mt-2">لا توجد بيانات أقسام</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">تحليل الفروع</h5>
                </div>
                <div class="card-body">
                    @if($report['branch_analysis']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="branchAnalysisTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>الفرع</th>
                                        <th>عدد المنتجات</th>
                                        <th>إجمالي الكمية</th>
                                        <th>إجمالي القيمة</th>
                                        <th>متوسط السعر</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['branch_analysis'] as $branch)
                                        <tr>
                                            <td><strong>{{ $branch->branch_name }}</strong></td>
                                            <td><span class="badge bg-info">{{ number_format($branch->products_count) }}</span></td>
                                            <td>{{ number_format($branch->total_qty) }}</td>
                                            <td>{{ number_format($branch->total_value, 2) }}</td>
                                            <td>{{ number_format($branch->avg_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-building-store-off display-1 text-muted"></i>
                            <p class="text-muted mt-2">لا توجد بيانات فروع</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Category by Branch Details -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">تفاصيل الأقسام حسب الفروع</h5>
                </div>
                <div class="card-body">
                    @if($report['category_analysis']['branch_inventory']->count() > 0)
                        <div class="accordion" id="categoryBranchAccordion">
                            @foreach($report['category_analysis']['branch_inventory'] as $categoryName => $branches)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse{{ $loop->index }}" aria-expanded="false">
                                            <strong>{{ $categoryName }}</strong>
                                            <span class="badge bg-primary ms-2">{{ $branches->count() }} فرع</span>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse" 
                                         data-bs-parent="#categoryBranchAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>الفرع</th>
                                                            <th>عدد المنتجات</th>
                                                            <th>الكمية</th>
                                                            <th>القيمة</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($branches as $branch)
                                                            <tr>
                                                                <td>{{ $branch->branch_name }}</td>
                                                                <td><span class="badge bg-info">{{ $branch->products_count }}</span></td>
                                                                <td>{{ number_format($branch->total_qty) }}</td>
                                                                <td>{{ number_format($branch->total_value, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-category-off display-1 text-muted"></i>
                            <p class="text-muted mt-2">لا توجد بيانات تفصيلية</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Inventory Report -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">المخزن الرئيسي</h5>
                </div>
                <div class="card-body">
                    @if($report['main_inventory']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm" id="mainInventoryTable">
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th>الكود</th>
                                        <th>الكمية</th>
                                        <th>الوحدة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['main_inventory'] as $item)
                                        <tr>
                                            <td>{{ $item->product->name ?? 'غير محدد' }}</td>
                                            <td>{{ $item->product->code ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->qty > 0 ? 'success' : 'danger' }}">
                                                    {{ number_format($item->qty) }}
                                                </span>
                                            </td>
                                            <td>{{ $item->product->unit->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-package-off display-1 text-muted"></i>
                            <p class="text-muted mt-2">لا توجد بيانات للمخزن الرئيسي</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Branch Inventory Report -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">الفروع</h5>
                </div>
                <div class="card-body">
                    @if($report['branch_inventory']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm" id="branchInventoryTable">
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th>الفرع</th>
                                        <th>الكمية</th>
                                        <th>السعر</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['branch_inventory'] as $item)
                                        <tr>
                                            <td>{{ $item->product_branch->product->name ?? 'غير محدد' }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $item->product_branch->branch->name ?? 'غير محدد' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->qty > 0 ? 'success' : 'danger' }}">
                                                    {{ number_format($item->qty) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($item->product_branch->price ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-building-store-off display-1 text-muted"></i>
                            <p class="text-muted mt-2">لا توجد بيانات للفروع</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Export Actions -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>تصدير البيانات</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-success" onclick="exportToExcel('mainInventoryTable', 'المخزن_الرئيسي_{{ $report['month'] }}')">
                            <i class="ti ti-file-spreadsheet"></i>
                            المخزن الرئيسي (Excel)
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="exportToExcel('branchInventoryTable', 'الفروع_{{ $report['month'] }}')">
                            <i class="ti ti-file-spreadsheet"></i>
                            الفروع (Excel)
                        </button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <h6>طباعة</h6>
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="ti ti-printer"></i>
                        طباعة التقرير
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<style>
    @media print {
        .btn, .card-header .dropdown, .card:last-child {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection

@section('js')
<script src="{{ asset('vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#mainInventoryTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
        },
        pageLength: 25,
        order: [[0, 'asc']]
    });

    $('#branchInventoryTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
        },
        pageLength: 25,
        order: [[0, 'asc']]
    });

    $('#categoryAnalysisTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
        },
        pageLength: 25,
        order: [[9, 'desc']], // Sort by total value descending
        columnDefs: [
            { targets: [1,2,3,4,5,6,7,8,9], className: 'text-center' }
        ]
    });

    $('#branchAnalysisTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
        },
        pageLength: 25,
        order: [[3, 'desc']], // Sort by total value descending
        columnDefs: [
            { targets: [1,2,3,4], className: 'text-center' }
        ]
    });
});

// Export to Excel function
function exportToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    const wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    XLSX.writeFile(wb, filename + '.xlsx');
}
</script>
@endsection
