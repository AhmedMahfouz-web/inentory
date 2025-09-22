@extends('layouts.dashboard')

@section('title', 'طلب منتجات جديد')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">طلب منتجات جديد</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('product-requests.store') }}" method="POST" id="product-request-form">
                        @csrf
                        
                        <!-- Request Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">الفرع <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">اختر الفرع</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>منخفض</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>عالي</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>عاجل</option>
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
                                          rows="3" placeholder="أضف أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
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
                                    <!-- Products will be added here -->
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
                                    <i class="ti ti-send me-1"></i>
                                    إرسال الطلب
                                </button>
                                <a href="{{ route('product-requests.index') }}" class="btn btn-outline-secondary">
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

<!-- Product Row Template -->
<template id="product-row-template">
    <div class="product-row border rounded p-3 mb-3">
        <div class="row align-items-end">
            <div class="col-md-5">
                <label class="form-label">المنتج <span class="text-danger">*</span></label>
                <select name="items[INDEX][product_id]" class="form-select product-select" required>
                    <option value="">اختر المنتج</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" 
                                data-stock="{{ $product->stock }}" 
                                data-price="{{ $product->price }}"
                                data-unit="{{ $product->unit->name ?? '' }}">
                            {{ $product->name }} ({{ $product->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الكمية <span class="text-danger">*</span></label>
                <input type="number" name="items[INDEX][quantity]" class="form-control quantity-input" 
                       min="0.01" step="0.01" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">المخزون المتاح</label>
                <input type="text" class="form-control stock-display" readonly placeholder="--">
            </div>
            <div class="col-md-2">
                <label class="form-label">الوحدة</label>
                <input type="text" class="form-control unit-display" readonly placeholder="--">
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
                <input type="text" name="items[INDEX][notes]" class="form-control" 
                       placeholder="ملاحظات خاصة بهذا المنتج...">
            </div>
        </div>
    </div>
</template>
@endsection

@section('scripts')
<script>
let productIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing product request form');
    
    const addProductBtn = document.getElementById('add-product-btn');
    const productsContainer = document.getElementById('products-container');

    console.log('Found elements:', {
        addProductBtn: !!addProductBtn,
        productsContainer: !!productsContainer
    });

    if (!addProductBtn || !productsContainer) {
        console.error('Required elements not found');
        return;
    }

    // Add first product row
    addProductRow();

    // Add product button click
    addProductBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Add product button clicked');
        addProductRow();
    });

    function addProductRow() {
        try {
            console.log('Adding product row, index:', productIndex);
            
            // Create the HTML directly
            const productRowHTML = `
                <div class="product-row border rounded p-3 mb-3">
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">المنتج <span class="text-danger">*</span></label>
                            <select name="items[${productIndex}][product_id]" class="form-select product-select" required>
                                <option value="">اختر المنتج</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-stock="{{ $product->stock }}" 
                                            data-price="{{ $product->price }}"
                                            data-unit="{{ $product->unit->name ?? '' }}">
                                        {{ $product->name }} ({{ $product->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الكمية <span class="text-danger">*</span></label>
                            <input type="number" name="items[${productIndex}][quantity]" class="form-control quantity-input" 
                                   min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">المخزون المتاح</label>
                            <input type="text" class="form-control stock-display" readonly placeholder="--">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الوحدة</label>
                            <input type="text" class="form-control unit-display" readonly placeholder="--">
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
                                   placeholder="ملاحظات خاصة بهذا المنتج...">
                        </div>
                    </div>
                </div>
            `;
            
            // Add the HTML to the container
            productsContainer.insertAdjacentHTML('beforeend', productRowHTML);
            
            // Get the newly added row
            const newRow = productsContainer.lastElementChild;
            console.log('New row added:', newRow);
            
            if (newRow) {
                setupProductRow(newRow);
            } else {
                console.error('Failed to add new row');
            }
            
            productIndex++;
        } catch (error) {
            console.error('Error adding product row:', error);
        }
    }

    function setupProductRow(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const stockDisplay = row.querySelector('.stock-display');
        const unitDisplay = row.querySelector('.unit-display');
        const removeBtn = row.querySelector('.remove-product-btn');

        console.log('Setting up product row:', row);
        console.log('Found elements:', {
            productSelect: !!productSelect,
            quantityInput: !!quantityInput,
            stockDisplay: !!stockDisplay,
            unitDisplay: !!unitDisplay,
            removeBtn: !!removeBtn
        });

        if (!productSelect || !quantityInput || !stockDisplay || !unitDisplay || !removeBtn) {
            console.error('Missing elements in product row');
            return;
        }

        // Product selection change
        productSelect.addEventListener('change', function() {
            console.log('Product selected:', this.value);
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const stock = selectedOption.dataset.stock;
                const unit = selectedOption.dataset.unit;
                
                console.log('Product data:', { stock, unit });
                
                stockDisplay.value = stock || '0';
                unitDisplay.value = unit || '';
                
                // Set max quantity to available stock
                quantityInput.max = stock;
                
                // Show warning if low stock
                if (parseFloat(stock) <= 10) {
                    stockDisplay.classList.add('text-warning');
                } else {
                    stockDisplay.classList.remove('text-warning');
                }
            } else {
                stockDisplay.value = '';
                unitDisplay.value = '';
                quantityInput.max = '';
            }
        });

        // Quantity validation
        quantityInput.addEventListener('input', function() {
            const maxStock = parseFloat(stockDisplay.value);
            const requestedQty = parseFloat(this.value);
            
            if (requestedQty > maxStock) {
                this.setCustomValidity('الكمية المطلوبة أكبر من المخزون المتاح');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
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

.stock-display.text-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.is-invalid {
    border-color: #dc3545;
}
</style>
@endsection
