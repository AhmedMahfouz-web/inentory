@extends('layouts.dashboard')

@section('title', 'تعديل طلب منتجات')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">تعديل طلب منتجات - {{ $productRequest->request_number }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('product-requests.update', $productRequest) }}" method="POST" id="product-request-form">
                        @csrf
                        @method('PUT')
                        
                        <!-- Request Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">الفرع <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">اختر الفرع</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" 
                                                {{ (old('branch_id', $productRequest->branch_id) == $branch->id) ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الأولوية <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="medium" {{ old('priority', $productRequest->priority) == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="low" {{ old('priority', $productRequest->priority) == 'low' ? 'selected' : '' }}>منخفض</option>
                                    <option value="high" {{ old('priority', $productRequest->priority) == 'high' ? 'selected' : '' }}>عالي</option>
                                    <option value="urgent" {{ old('priority', $productRequest->priority) == 'urgent' ? 'selected' : '' }}>عاجل</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">ملاحظات</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                          rows="3" placeholder="أضف أي ملاحظات إضافية...">{{ old('notes', $productRequest->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Products Section -->
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">المنتجات المطلوبة</h6>
                                <button type="button" class="btn btn-sm btn-primary" id="add-product-btn">
                                    <i class="ti ti-plus me-1"></i>
                                    إضافة منتج
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="products-container">
                                    <!-- Existing products will be loaded here -->
                                </div>
                                
                                @error('items')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ti ti-check me-1"></i>
                                    حفظ التعديلات
                                </button>
                                <a href="{{ route('product-requests.show', $productRequest) }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    إلغاء
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('js/forms-selects.js') }}"></script>
<script>
let productIndex = 0;
const existingItems = @json($productRequest->items);

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing product request edit form');
    console.log('Existing items:', existingItems);
    
    const addProductBtn = document.getElementById('add-product-btn');
    const productsContainer = document.getElementById('products-container');

    if (!addProductBtn || !productsContainer) {
        console.error('Required elements not found');
        return;
    }

    // Load existing products
    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => {
            addProductRow(item);
        });
    } else {
        // Add one empty row if no existing items
        addProductRow();
    }

    // Add product button click
    addProductBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Add product button clicked');
        addProductRow();
    });

    function addProductRow(existingItem = null) {
        try {
            console.log('Adding product row, index:', productIndex, 'existing:', existingItem);
            
            const productRowHTML = `
                <div class="product-row border rounded p-3 mb-3">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">المنتج <span class="text-danger">*</span></label>
                            <select name="items[${productIndex}][product_id]" class="select2 form-select product-select" data-allow-clear="true" required>
                                <option value="">اختر المنتج</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-unit="{{ $product->unit->name ?? '' }}"
                                            ${existingItem && existingItem.product_id == {{ $product->id }} ? 'selected' : ''}>
                                        {{ $product->code }} - {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الكمية <span class="text-danger">*</span></label>
                            <input type="number" name="items[${productIndex}][quantity]" class="form-control quantity-input" 
                                   min="0.01" step="0.01" value="${existingItem ? existingItem.requested_qty : ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الوحدة</label>
                            <input type="text" class="form-control unit-display" readonly placeholder="--" 
                                   value="${existingItem && existingItem.product ? (existingItem.product.unit ? existingItem.product.unit.name : '') : ''}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-product-btn">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <label class="form-label">ملاحظات</label>
                            <input type="text" name="items[${productIndex}][notes]" class="form-control" 
                                   placeholder="ملاحظات خاصة بهذا المنتج..." 
                                   value="${existingItem && existingItem.notes ? existingItem.notes : ''}">
                        </div>
                    </div>
                </div>
            `;
            
            productsContainer.insertAdjacentHTML('beforeend', productRowHTML);
            const newRow = productsContainer.lastElementChild;
            
            if (newRow) {
                setupProductRow(newRow);
            }
            
            productIndex++;
        } catch (error) {
            console.error('Error adding product row:', error);
        }
    }

    function setupProductRow(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const unitDisplay = row.querySelector('.unit-display');
        const removeBtn = row.querySelector('.remove-product-btn');

        if (!productSelect || !quantityInput || !unitDisplay || !removeBtn) {
            console.error('Missing elements in product row');
            return;
        }

        // Initialize select2 for this row
        $(productSelect).select2({
            placeholder: 'اختر المنتج',
            allowClear: true
        });

        // Product selection change
        $(productSelect).on('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const unit = selectedOption.dataset.unit;
                unitDisplay.value = unit || '';
            } else {
                unitDisplay.value = '';
            }
        });

        // Remove product row
        removeBtn.addEventListener('click', function() {
            if (productsContainer.children.length > 1) {
                row.remove();
            } else {
                alert('يجب أن يحتوي الطلب على منتج واحد على الأقل');
            }
        });
    }

    // Form validation
    document.getElementById('product-request-form').addEventListener('submit', function(e) {
        const productRows = productsContainer.children.length;
        if (productRows === 0) {
            e.preventDefault();
            alert('يجب إضافة منتج واحد على الأقل');
            return false;
        }

        // Check for duplicate products
        const selectedProducts = [];
        const productSelects = document.querySelectorAll('.product-select');
        
        for (let select of productSelects) {
            if (select.value) {
                if (selectedProducts.includes(select.value)) {
                    e.preventDefault();
                    alert('لا يمكن إضافة نفس المنتج أكثر من مرة');
                    return false;
                }
                selectedProducts.push(select.value);
            }
        }
    });
});
</script>

<style>
.product-row {
    background-color: #f8f9fa;
}

.product-row:hover {
    background-color: #e9ecef;
}

.is-invalid {
    border-color: #dc3545;
}
</style>
@endsection
