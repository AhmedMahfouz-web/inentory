@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <!-- Current Month Status Card -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">حالة الشهر الحالي</h5>
                    <span class="badge bg-primary">{{ $currentMonth }}</span>
                </div>
                <div class="card-body">
                    @if($currentMonthExists['any_exists'])
                        <div class="alert alert-success">
                            <i class="ti ti-check"></i>
                            تم إنشاء بداية الشهر الحالي
                        </div>
                        
                        @if($currentReport)
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-package"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">المخزن الرئيسي</small>
                                            <div class="h6 mb-0">{{ $currentReport['summary']['main_products_count'] }} منتج</div>
                                            <small class="text-muted">إجمالي: {{ number_format($currentReport['summary']['total_main_qty']) }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-info">
                                                <i class="ti ti-building-store"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">الفروع</small>
                                            <div class="h6 mb-0">{{ $currentReport['summary']['branch_products_count'] }} منتج</div>
                                            <small class="text-muted">إجمالي: {{ number_format($currentReport['summary']['total_branch_qty']) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle"></i>
                            لم يتم إنشاء بداية الشهر الحالي بعد
                        </div>
                        
                        <form method="POST" action="{{ route('generate current month starts') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-plus"></i>
                                إنشاء بداية الشهر الحالي
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Previous Month Status Card -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">حالة الشهر السابق</h5>
                    <span class="badge bg-secondary">{{ $previousMonth }}</span>
                </div>
                <div class="card-body">
                    @if($previousMonthExists['any_exists'])
                        <div class="alert alert-success">
                            <i class="ti ti-check"></i>
                            يوجد بيانات للشهر السابق
                        </div>
                        <p class="text-muted mb-0">
                            يمكن إنشاء بداية الشهر الحالي بناءً على نهاية الشهر السابق
                        </p>
                    @else
                        <div class="alert alert-danger">
                            <i class="ti ti-x"></i>
                            لا توجد بيانات للشهر السابق
                        </div>
                        <p class="text-muted mb-0">
                            يجب إنشاء بيانات الشهر السابق أولاً أو إدخال البيانات يدوياً
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Month Generation -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">إنشاء بداية شهر محدد</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('generate month starts') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">الشهر</label>
                        <input type="month" name="month" class="form-control" value="{{ $currentMonth }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">النوع</label>
                        <select name="type" class="form-select" required>
                            <option value="both">المخزن الرئيسي والفروع</option>
                            <option value="main">المخزن الرئيسي فقط</option>
                            <option value="branch">الفروع فقط</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-calendar-plus"></i>
                            إنشاء
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">إجراءات سريعة</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="{{ route('monthly starts report') }}" class="btn btn-outline-primary w-100">
                        <i class="ti ti-report"></i>
                        عرض تقرير الشهر الحالي
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('store start auto mysql') }}" class="btn btn-outline-info w-100">
                        <i class="ti ti-database"></i>
                        إنشاء بداية الفروع (MySQL)
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('auto generate main inventory') }}" class="btn btn-outline-success w-100">
                        <i class="ti ti-package"></i>
                        إنشاء بداية المخزن الرئيسي
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    // Auto-refresh status every 30 seconds
    setInterval(function() {
        // You can add AJAX calls here to update status without page refresh
    }, 30000);
</script>
@endsection
