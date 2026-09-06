@extends('layouts.dashboard')

@section('title', 'تفاصيل الطلب')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- Request Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تفاصيل الطلب #{{ $productRequest->request_number }}</h5>
                    <div>
                        @if($productRequest->status === 'pending')
                            <span class="badge bg-warning">قيد الانتظار</span>
                        @elseif($productRequest->status === 'approved')
                            <span class="badge bg-info">تمت الموافقة</span>
                        @elseif($productRequest->status === 'fulfilled')
                            <span class="badge bg-success">تم التنفيذ</span>
                        @elseif($productRequest->status === 'rejected')
                            <span class="badge bg-danger">مرفوض</span>
                        @elseif($productRequest->status === 'cancelled')
                            <span class="badge bg-secondary">ملغي</span>
                        @endif

                        @if($productRequest->priority === 'urgent')
                            <span class="badge bg-danger ms-2">عاجل</span>
                        @elseif($productRequest->priority === 'high')
                            <span class="badge bg-warning ms-2">عالي</span>
                        @elseif($productRequest->priority === 'medium')
                            <span class="badge bg-info ms-2">متوسط</span>
                        @else
                            <span class="badge bg-secondary ms-2">منخفض</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">رقم الطلب:</th>
                                    <td>{{ $productRequest->request_number }}</td>
                                </tr>
                                <tr>
                                    <th>الفرع:</th>
                                    <td>{{ $productRequest->branch->name }}</td>
                                </tr>
                                <tr>
                                    <th>الأولوية:</th>
                                    <td>
                                        @if($productRequest->priority === 'urgent')
                                            عاجل
                                        @elseif($productRequest->priority === 'high')
                                            عالي
                                        @elseif($productRequest->priority === 'medium')
                                            متوسط
                                        @else
                                            منخفض
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>الحالة:</th>
                                    <td>
                                        @if($productRequest->status === 'pending')
                                            قيد الانتظار
                                        @elseif($productRequest->status === 'approved')
                                            تمت الموافقة
                                        @elseif($productRequest->status === 'fulfilled')
                                            تم التنفيذ
                                        @elseif($productRequest->status === 'rejected')
                                            مرفوض
                                        @elseif($productRequest->status === 'cancelled')
                                            ملغي
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">تاريخ الطلب:</th>
                                    <td>{{ $productRequest->requested_at ? $productRequest->requested_at->format('Y-m-d H:i') : $productRequest->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>طلب بواسطة:</th>
                                    <td>{{ $productRequest->requestedBy->name ?? 'غير محدد' }}</td>
                                </tr>
                                @if($productRequest->approved_at)
                                <tr>
                                    <th>تاريخ الموافقة:</th>
                                    <td>{{ $productRequest->approved_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>تمت الموافقة بواسطة:</th>
                                    <td>{{ $productRequest->approvedBy->name ?? 'غير محدد' }}</td>
                                </tr>
                                @endif
                                @if($productRequest->fulfilled_at)
                                <tr>
                                    <th>تاريخ التنفيذ:</th>
                                    <td>{{ $productRequest->fulfilled_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>تم التنفيذ بواسطة:</th>
                                    <td>{{ $productRequest->fulfilledBy->name ?? 'غير محدد' }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($productRequest->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>ملاحظات الطلب:</h6>
                            <p class="text-muted">{{ $productRequest->notes }}</p>
                        </div>
                    </div>
                    @endif

                    @if($productRequest->warehouse_notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>ملاحظات المخزن:</h6>
                            <p class="text-muted">{{ $productRequest->warehouse_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Request Items -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">المنتجات المطلوبة</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>كود المنتج</th>
                                    <th>اسم المنتج</th>
                                    <th>الكمية المطلوبة</th>
                                    <th>الكمية المعتمدة</th>
                                    <th>الكمية المنفذة</th>
                                    <th>الوحدة</th>
                                    <th>الحالة</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productRequest->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product->code }}</td>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->requested_quantity }}</td>
                                    <td>{{ $item->approved_quantity ?? '--' }}</td>
                                    <td>{{ $item->fulfilled_quantity ?? '--' }}</td>
                                    <td>{{ $item->product->unit->name ?? '' }}</td>
                                    <td>
                                        @if($item->status === 'pending')
                                            <span class="badge bg-warning">قيد الانتظار</span>
                                        @elseif($item->status === 'approved')
                                            <span class="badge bg-info">معتمد</span>
                                        @elseif($item->status === 'fulfilled')
                                            <span class="badge bg-success">منفذ</span>
                                        @elseif($item->status === 'rejected')
                                            <span class="badge bg-danger">مرفوض</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->notes ?? '--' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('product-requests.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            العودة للقائمة
                        </a>
                        
                        <div>
                            @if($productRequest->status === 'pending' && (auth()->user()->id == $productRequest->requested_by || auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager')))
                                <a href="{{ route('product-requests.edit', $productRequest) }}" class="btn btn-warning me-2">
                                    <i class="ti ti-edit me-1"></i>
                                    تعديل الطلب
                                </a>
                            @endif

                            @if($productRequest->status === 'pending' && auth()->user()->can('product-request-edit'))
                                <form action="{{ route('product-requests.cancel', $productRequest) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ti ti-x me-1"></i>
                                        إلغاء الطلب
                                    </button>
                                </form>
                            @endif

                            @if($productRequest->status === 'pending' && auth()->user()->can('product-request-approve'))
                                <a href="{{ route('product-requests.show-approve', $productRequest) }}" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>
                                    الموافقة على الطلب
                                </a>
                            @endif

                            @if($productRequest->status === 'approved' && auth()->user()->can('product-request-fulfill'))
                                <a href="{{ route('product-requests.show-fulfill', $productRequest) }}" class="btn btn-success">
                                    <i class="ti ti-package me-1"></i>
                                    تنفيذ الطلب
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
