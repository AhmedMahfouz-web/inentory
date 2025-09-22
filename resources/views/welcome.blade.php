@extends('layouts.dashboard')

@section('css')
    <style>
        .btn-outline-primary:hover {
            background: #7367f0 !important;
            color: #f4f3fe !important;
        }
        
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 3rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .timeline-point {
            position: absolute;
            left: -2.5rem;
            top: 0.25rem;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            z-index: 1;
        }
        
        .timeline-point-danger {
            background: #ff3e1d;
            color: white;
        }
        
        .timeline-point-info {
            background: #03c3ec;
            color: white;
        }
        
        .timeline-point-success {
            background: #71dd37;
            color: white;
        }
        
        .timeline-event {
            background: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            border-left: 3px solid #e9ecef;
        }
        
        .timeline-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .timeline-title {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .timeline-text {
            margin: 0;
            font-size: 0.8125rem;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <!-- Enhanced Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-primary mb-2">
                        <i class="menu-icon tf-icons ti ti-box ti-sm mx-0"></i>
                    </div>
                    <h5 class="card-title mb-2">المنتجات</h5>
                    <h6>{{ number_format($dashboardStats['total_products']) }}</h6>
                    <p class="text-light fw-semibold mb-1">إجمالي الأصناف</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-info mb-2">
                        <i class="menu-icon tf-icons ti ti-building-warehouse ti-sm mx-0"></i>
                    </div>
                    <h5 class="card-title mb-2">الفروع</h5>
                    <h6>{{ number_format($dashboardStats['total_branches']) }}</h6>
                    <p class="text-light fw-semibold mb-1">إجمالي المخازن</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-danger mb-2">
                        <i class="menu-icon tf-icons ti ti-shopping-cart ti-sm mx-0"></i>
                    </div>
                    <h5 class="card-title mb-2">الوارد</h5>
                    <h6>{{ number_format($total_income, 2, '.', ',') }}ج</h6>
                    <p class="text-light fw-semibold mb-1">مخزن رئيسي</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-success mb-2">
                        <i class="menu-icon tf-icons ti ti-currency-dollar ti-sm mx-0"></i>
                    </div>
                    <h5 class="card-title mb-2">الصادر</h5>
                    <h6>{{ number_format($total_sells, 2, '.', ',') }}ج</h6>
                    <p class="text-light fw-semibold mb-1">مخزن رئيسي</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-warning mb-2">
                        <i class="menu-icon tf-icons ti ti-chart-line ti-sm mx-0"></i>
                    </div>
                    <h5 class="card-title mb-2">المعاملات</h5>
                    <h6>{{ number_format($dashboardStats['monthly_transactions']) }}</h6>
                    <p class="text-light fw-semibold mb-1">هذا الشهر</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-dark mb-2">
                        <i class="menu-icon tf-icons ti ti-coins ti-sm mx-0"></i>
                    </div>
                    <h5 class="card-title mb-2">قيمة المخزون</h5>
                    <h6>{{ number_format($dashboardStats['total_inventory_value'], 0, '.', ',') }}ج</h6>
                    <p class="text-light fw-semibold mb-1">إجمالي القيمة</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Starts Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">حالة بداية الشهر</h5>
                    <span class="badge bg-primary">{{ $monthlyStartsStatus['current_month'] }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($monthlyStartsStatus['exists']['main_inventory'])
                                <div class="alert alert-success">
                                    <i class="ti ti-check me-2"></i>
                                    المخزن الرئيسي: {{ $monthlyStartsStatus['main_starts_count'] }} منتج
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    المخزن الرئيسي: غير مكتمل
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if($monthlyStartsStatus['exists']['branch_inventory'])
                                <div class="alert alert-success">
                                    <i class="ti ti-check me-2"></i>
                                    الفروع: {{ $monthlyStartsStatus['branch_starts_count'] }} منتج
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    الفروع: غير مكتمل
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('monthly starts') }}" class="btn btn-primary w-100">
                                <i class="ti ti-calendar-stats me-2"></i>
                                إدارة بداية الشهر
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access (Existing Content Enhanced) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">الوصول السريع</h5>
                <div class="card-body">
                    <small class="text-light fw-semibold">المخازن</small>
                    <div class="demo-inline-spacing">
                        <a href="{{ route('product inventory') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-database"></i> مخزن رئيسي</a>
                        @foreach ($branches as $branch)
                            <a href="{{ route('inventory', $branch->id) }}" class="btn rounded-pill btn-outline-primary"><i
                                    class="menu-icon tf-icons ti ti-database"></i> {{ $branch->name }}</a>
                        @endforeach
                    </div>
                </div>
                <hr class="m-0" />
                <div class="card-body">
                    <small class="text-light fw-semibold">الحركات</small>
                    <div class="demo-inline-spacing">
                        <a href="{{ route('show order') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-clipboard-text"></i> اذون الصرف</a>
                        <a href="{{ route('create exchange product') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-transfer-out"></i> تحويل</a>
                        <a href="{{ route('exchanged product') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-transfer-out"></i> عرض التحويلات</a>
                        <a href="{{ route('create increase product') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-transfer-in"></i> اضافة</a>
                        <a href="{{ route('increased product') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-transfer-in"></i> عرض الاضافات</a>
                    </div>
                </div>
                <hr class="m-0" />
                <div class="card-body">
                    <small class="text-light fw-semibold">بداية الشهر</small>
                    <div class="demo-inline-spacing">
                        <a href="{{ route('monthly starts') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-calendar-stats"></i> لوحة التحكم</a>
                        <a href="{{ route('monthly starts report') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-report"></i> التقارير</a>
                        <a href="{{ route('start inventory') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-package"></i> المخزن الرئيسي</a>
                    </div>
                </div>
                <hr class="m-0" />
                <div class="card-body">
                    <small class="text-light fw-semibold">الاعدادات</small>
                    <div class="demo-inline-spacing">
                        <a href="{{ route('show suppliers') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-truck-delivery"></i> الموردون</a>
                        <a href="{{ route('show branches') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon tf-icons ti ti-building-warehouse"></i> المخازن الفرعية</a>
                        <a href="{{ route('show products') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon ti ti-box"></i> الاصناف</a>
                        <a href="{{ route('show categories') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon ti ti-category"></i> الاقسام</a>
                        <a href="{{ route('show sub_categories') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon ti ti-category-2"></i> التصنيفات</a>
                        <a href="{{ route('show units') }}" class="btn rounded-pill btn-outline-primary"><i
                                class="menu-icon ti ti-weight"></i> الوحدات</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance & Low Stock Alerts -->
    <div class="row mb-4">
        <!-- Branch Performance -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">أداء الفروع - الشهر الحالي</h5>
                </div>
                <div class="card-body">
                    @if($branchPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الفرع</th>
                                        <th>عدد المنتجات</th>
                                        <th>المبيعات (كمية)</th>
                                        <th>المبيعات (قيمة)</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branchPerformance as $branch)
                                        <tr>
                                            <td><strong>{{ $branch['name'] }}</strong></td>
                                            <td><span class="badge bg-info">{{ $branch['products_count'] }}</span></td>
                                            <td>{{ number_format($branch['monthly_sales_qty']) }}</td>
                                            <td>{{ number_format($branch['monthly_sales_value'], 2) }}ج</td>
                                            <td>
                                                <a href="{{ route('inventory', $branch['id']) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
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

        <!-- Low Stock Alerts -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">تنبيهات المخزون المنخفض</h5>
                    <span class="badge bg-danger">{{ $lowStockProducts->count() }}</span>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($lowStockProducts as $product)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="mb-1">{{ $product->name }}</h6>
                                        <small class="text-muted">{{ $product->sub_category->name ?? 'غير محدد' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger">{{ $product->stock }}</span>
                                        <small class="text-muted d-block">{{ $product->unit->name ?? '' }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('show products') }}" class="btn btn-outline-danger btn-sm w-100">
                                عرض جميع المنتجات
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-check-circle display-1 text-success"></i>
                            <p class="text-muted mt-2">جميع المنتجات في مستوى آمن</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">النشاط الأخير</h5>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        <div class="timeline">
                            @foreach($recentActivity as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-{{ $activity['color'] }}">
                                        <i class="ti {{ $activity['icon'] }}"></i>
                                    </div>
                                    <div class="timeline-event">
                                        <div class="timeline-header">
                                            <h6 class="timeline-title">{{ $activity['description'] }}</h6>
                                            <small class="text-muted">{{ $activity['created_at']->diffForHumans() }}</small>
                                        </div>
                                        <p class="timeline-text">
                                            الكمية: <span class="badge bg-{{ $activity['color'] }}">{{ number_format($activity['quantity']) }}</span>
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-activity-off display-1 text-muted"></i>
                            <p class="text-muted mt-2">لا يوجد نشاط حديث</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
