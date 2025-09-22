@extends('layouts.dashboard')

@section('title', 'لوحة تحكم أمين المخزن')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-1">لوحة تحكم أمين المخزن</h4>
                            <p class="text-muted mb-0">إدارة طلبات المنتجات من الفروع</p>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <div class="text-center">
                                    <div class="avatar avatar-sm bg-label-warning">
                                        <i class="ti ti-clock"></i>
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $statistics['pending_requests'] }} في الانتظار</small>
                                </div>
                                <div class="text-center">
                                    <div class="avatar avatar-sm bg-label-danger">
                                        <i class="ti ti-alert-triangle"></i>
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $urgentRequests->count() }} عاجل</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-md bg-label-primary mx-auto mb-2">
                        <i class="ti ti-file-text"></i>
                    </div>
                    <h4 class="mb-1">{{ $statistics['total_requests'] }}</h4>
                    <small class="text-muted">إجمالي الطلبات</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-md bg-label-warning mx-auto mb-2">
                        <i class="ti ti-clock"></i>
                    </div>
                    <h4 class="mb-1">{{ $statistics['pending_requests'] }}</h4>
                    <small class="text-muted">في الانتظار</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-md bg-label-success mx-auto mb-2">
                        <i class="ti ti-check"></i>
                    </div>
                    <h4 class="mb-1">{{ $statistics['fulfilled_requests'] }}</h4>
                    <small class="text-muted">تم التنفيذ</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-md bg-label-info mx-auto mb-2">
                        <i class="ti ti-trending-up"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($statistics['estimated_value'], 2) }}</h4>
                    <small class="text-muted">القيمة المقدرة</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Urgent Requests -->
        @if($urgentRequests->count() > 0)
        <div class="col-12 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-label-danger">
                    <h5 class="card-title text-danger mb-0">
                        <i class="ti ti-alert-triangle me-2"></i>
                        طلبات عاجلة ({{ $urgentRequests->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>الفرع</th>
                                    <th>تاريخ الطلب</th>
                                    <th>عدد الأصناف</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($urgentRequests as $request)
                                <tr>
                                    <td><strong class="text-danger">{{ $request->request_number }}</strong></td>
                                    <td>{{ $request->branch->name }}</td>
                                    <td>{{ $request->requested_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $request->total_items }}</td>
                                    <td>
                                        <span class="badge bg-{{ $request->status_color }}">{{ $request->status_label }}</span>
                                    </td>
                                    <td>
                                        @if($request->canBeApproved())
                                            <a href="{{ route('product-requests.show-approve', $request) }}" class="btn btn-sm btn-success">
                                                <i class="ti ti-check"></i> مراجعة
                                            </a>
                                        @elseif($request->canBeFulfilled())
                                            <a href="{{ route('product-requests.show-fulfill', $request) }}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-truck"></i> تنفيذ
                                            </a>
                                        @endif
                                        <a href="{{ route('product-requests.show', $request) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Pending Requests -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">الطلبات المعلقة</h5>
                    <small class="text-muted">{{ $pendingRequests->count() }} طلب</small>
                </div>
                <div class="card-body">
                    @forelse($pendingRequests as $request)
                        <div class="d-flex align-items-center mb-3 p-3 border rounded">
                            <div class="avatar avatar-sm bg-label-{{ $request->priority_color }} me-3">
                                <i class="ti ti-file-text"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $request->request_number }}</h6>
                                <div class="d-flex align-items-center gap-3">
                                    <small class="text-muted">
                                        <i class="ti ti-building me-1"></i>
                                        {{ $request->branch->name }}
                                    </small>
                                    <small class="text-muted">
                                        <i class="ti ti-clock me-1"></i>
                                        {{ $request->requested_at->diffForHumans() }}
                                    </small>
                                    <span class="badge bg-{{ $request->priority_color }}">{{ $request->priority_label }}</span>
                                    @if($request->is_overdue)
                                        <span class="badge bg-danger">متأخر</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $request->total_items }} صنف - {{ $request->notes ?? 'بدون ملاحظات' }}</small>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('product-requests.show-approve', $request) }}" class="btn btn-sm btn-success">
                                    <i class="ti ti-check"></i>
                                </a>
                                <a href="{{ route('product-requests.show', $request) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="ti ti-check-circle display-1 text-success"></i>
                            <h5 class="mt-2">لا توجد طلبات معلقة</h5>
                            <p class="text-muted">جميع الطلبات تم معالجتها</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Popular Products & Overdue -->
        <div class="col-lg-4">
            <!-- Popular Products -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">المنتجات الأكثر طلباً</h5>
                </div>
                <div class="card-body">
                    @forelse($popularProducts->take(5) as $product)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-primary me-3">
                                <i class="ti ti-box"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $product->name }}</h6>
                                <small class="text-muted">{{ $product->request_count }} طلب</small>
                            </div>
                            <div class="text-end">
                                <small class="text-primary">{{ $product->total_requested }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3">
                            <i class="ti ti-box text-muted"></i>
                            <p class="text-muted mb-0">لا توجد بيانات</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Overdue Requests -->
            @if($overdueRequests->count() > 0)
            <div class="card">
                <div class="card-header bg-label-warning">
                    <h5 class="card-title text-warning mb-0">
                        <i class="ti ti-clock-exclamation me-2"></i>
                        طلبات متأخرة
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($overdueRequests->take(5) as $request)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-warning me-3">
                                <i class="ti ti-alert-triangle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $request->request_number }}</h6>
                                <small class="text-muted">{{ $request->branch->name }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-warning">{{ $request->requested_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);

// Show notification for urgent requests
@if($urgentRequests->count() > 0)
    // You can add notification logic here
@endif
</script>
@endsection
