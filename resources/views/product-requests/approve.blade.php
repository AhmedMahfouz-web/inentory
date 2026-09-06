@extends('layouts.dashboard')

@section('title', 'الموافقة على الطلب')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <!-- Request Header -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">الموافقة على الطلب #{{ $productRequest->request_number }}</h5>
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
                                            @if ($productRequest->priority === 'urgent')
                                                <span class="badge bg-danger">عاجل</span>
                                            @elseif($productRequest->priority === 'high')
                                                <span class="badge bg-warning">عالي</span>
                                            @elseif($productRequest->priority === 'medium')
                                                <span class="badge bg-info">متوسط</span>
                                            @else
                                                <span class="badge bg-secondary">منخفض</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">تاريخ الطلب:</th>
                                        <td>{{ $productRequest->requested_at ? $productRequest->requested_at->format('Y-m-d H:i') : $productRequest->created_at->format('Y-m-d H:i') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>طلب بواسطة:</th>
                                        <td>{{ $productRequest->requestedBy->name ?? 'غير محدد' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if ($productRequest->notes)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>ملاحظات الطلب:</h6>
                                    <p class="text-muted">{{ $productRequest->notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Approval Form -->
                <form action="{{ route('product-requests.approve', $productRequest) }}" method="POST">
                    @csrf

                    <!-- Products -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">المنتجات المطلوبة</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">كود المنتج</th>
                                            <th width="20%">اسم المنتج</th>
                                            <th width="10%">الكمية المطلوبة</th>
                                            <th width="10%">المخزون المتاح</th>
                                            <th width="10%">الوحدة</th>
                                            <th width="10%">الإجراء</th>
                                            <th width="10%">الكمية المعتمدة</th>
                                            <th width="10%">ملاحظات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productRequest->items as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->product->code }}</td>
                                                <td>{{ $item->product->name }}</td>
                                                <td>{{ $item->requested_qty }}</td>
                                                <td>
                                                    <span
                                                        class="badge {{ $item->product->stock >= $item->requested_quantity ? 'bg-success' : 'bg-warning' }}">
                                                        {{ $item->product->stock }}
                                                    </span>
                                                </td>
                                                <td>{{ $item->product->unit->name ?? '' }}</td>
                                                <td>
                                                    <select name="items[{{ $item->id }}][action]"
                                                        class="form-select form-select-sm action-select"
                                                        data-item-id="{{ $item->id }}" required>
                                                        <option value="approve" selected>موافقة</option>
                                                        <option value="reject">رفض</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $item->id }}][quantity]"
                                                        class="form-control form-control-sm quantity-input"
                                                        data-item-id="{{ $item->id }}"
                                                        value="{{ $item->requested_quantity }}" min="0"
                                                        step="0.01" max="{{ $item->product->stock }}" required>
                                                    <small class="text-muted">الحد الأقصى:
                                                        {{ $item->product->stock }}</small>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $item->id }}][notes]"
                                                        class="form-control form-control-sm" placeholder="ملاحظات...">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Warehouse Notes -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">ملاحظات المخزن</h5>
                        </div>
                        <div class="card-body">
                            <textarea name="warehouse_notes" class="form-control" rows="4" placeholder="أضف ملاحظات المخزن هنا...">{{ old('warehouse_notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('product-requests.warehouse-dashboard') }}"
                                    class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    إلغاء
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>
                                    حفظ الموافقة
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle action select changes
            const actionSelects = document.querySelectorAll('.action-select');

            actionSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const itemId = this.dataset.itemId;
                    const quantityInput = document.querySelector(
                        `.quantity-input[data-item-id="${itemId}"]`);

                    if (this.value === 'reject') {
                        quantityInput.value = 0;
                        quantityInput.disabled = true;
                        quantityInput.required = false;
                    } else {
                        quantityInput.disabled = false;
                        quantityInput.required = true;
                    }
                });
            });

            // Validate quantities don't exceed stock
            const quantityInputs = document.querySelectorAll('.quantity-input');

            quantityInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const max = parseFloat(this.max);
                    const value = parseFloat(this.value);

                    if (value > max) {
                        this.setCustomValidity('الكمية المعتمدة أكبر من المخزون المتاح');
                        this.classList.add('is-invalid');
                    } else {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>

    <style>
        .is-invalid {
            border-color: #dc3545;
        }
    </style>
@endsection
