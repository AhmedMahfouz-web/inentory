@extends('layouts.dashboard')

@section('title', 'طلبات المنتجات')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">طلبات المنتجات</h5>
                    <div>
                        <a href="{{ route('product-requests.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            طلب جديد
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('product-requests.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">الفرع</label>
                                <select name="branch_id" class="form-select">
                                    <option value="">جميع الفروع</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>في الانتظار</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                    <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>تم التنفيذ</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="ti ti-search"></i>
                                    بحث
                                </button>
                                <a href="{{ route('product-requests.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-refresh"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-label-primary">
                                <div class="card-body text-center">
                                    <i class="ti ti-file-text display-6 text-primary"></i>
                                    <h4 class="text-primary mb-1">{{ $statistics['total_requests'] }}</h4>
                                    <small>إجمالي الطلبات</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-label-warning">
                                <div class="card-body text-center">
                                    <i class="ti ti-clock display-6 text-warning"></i>
                                    <h4 class="text-warning mb-1">{{ $statistics['pending_requests'] }}</h4>
                                    <small>في الانتظار</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-label-success">
                                <div class="card-body text-center">
                                    <i class="ti ti-check display-6 text-success"></i>
                                    <h4 class="text-success mb-1">{{ $statistics['fulfilled_requests'] }}</h4>
                                    <small>تم التنفيذ</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-label-danger">
                                <div class="card-body text-center">
                                    <i class="ti ti-x display-6 text-danger"></i>
                                    <h4 class="text-danger mb-1">{{ $statistics['rejected_requests'] }}</h4>
                                    <small>مرفوض</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>الفرع</th>
                                    <th>تاريخ الطلب</th>
                                    <th>الأولوية</th>
                                    <th>عدد الأصناف</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td>
                                            <strong>{{ $request->request_number }}</strong>
                                        </td>
                                        <td>{{ $request->branch->name }}</td>
                                        <td>{{ $request->requested_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $request->priority_color }}">
                                                {{ $request->priority_label }}
                                            </span>
                                        </td>
                                        <td>{{ $request->total_items }}</td>
                                        <td>
                                            <span class="badge bg-{{ $request->status_color }}">
                                                {{ $request->status_label }}
                                            </span>
                                            @if($request->is_overdue)
                                                <span class="badge bg-danger ms-1">متأخر</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('product-requests.show', $request) }}">
                                                        <i class="ti ti-eye me-1"></i>
                                                        عرض التفاصيل
                                                    </a>
                                                    @if($request->canBeCancelled())
                                                        <form action="{{ route('product-requests.cancel', $request) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-danger" 
                                                                    onclick="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟')">
                                                                <i class="ti ti-x me-1"></i>
                                                                إلغاء الطلب
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="empty-state">
                                                <i class="ti ti-file-text display-1 text-muted"></i>
                                                <h5 class="mt-2">لا توجد طلبات</h5>
                                                <p class="text-muted">لم يتم العثور على أي طلبات منتجات</p>
                                                <a href="{{ route('product-requests.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-1"></i>
                                                    إنشاء طلب جديد
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.empty-state {
    padding: 3rem 1rem;
}

.card.bg-label-primary { border-left: 4px solid var(--bs-primary); }
.card.bg-label-warning { border-left: 4px solid var(--bs-warning); }
.card.bg-label-success { border-left: 4px solid var(--bs-success); }
.card.bg-label-danger { border-left: 4px solid var(--bs-danger); }
</style>
@endsection

@section('scripts')
<script>
// Auto-refresh pending requests count
function updatePendingCount() {
    fetch('{{ route("product-requests.api.pending-count") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('pending-requests-badge');
                if (data.pending_count > 0) {
                    badge.textContent = data.pending_count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error updating pending count:', error));
}

// Update count on page load and every 30 seconds
updatePendingCount();
setInterval(updatePendingCount, 30000);
</script>
@endsection
