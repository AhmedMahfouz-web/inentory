@extends('layouts.dashboard')

@section('title', 'استيراد البيانات')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">استيراد البيانات من Excel</h5>
                    <small class="text-muted">يمكنك استيراد المنتجات والأقسام والوحدات من ملفات Excel</small>
                </div>
                <div class="card-body">
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-x me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Import Tabs -->
                    <ul class="nav nav-pills mb-4" id="importTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="products-tab" data-bs-toggle="pill" data-bs-target="#products" type="button" role="tab">
                                <i class="ti ti-package me-1"></i>
                                المنتجات
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="categories-tab" data-bs-toggle="pill" data-bs-target="#categories" type="button" role="tab">
                                <i class="ti ti-category me-1"></i>
                                الأقسام
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sub-categories-tab" data-bs-toggle="pill" data-bs-target="#sub-categories" type="button" role="tab">
                                <i class="ti ti-category-2 me-1"></i>
                                الأقسام الفرعية
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="units-tab" data-bs-toggle="pill" data-bs-target="#units" type="button" role="tab">
                                <i class="ti ti-ruler me-1"></i>
                                الوحدات
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="importTabsContent">
                        
                        <!-- Products Import -->
                        <div class="tab-pane fade show active" id="products" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h6 class="mb-0">استيراد المنتجات</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('imports.products') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="products-file" class="form-label">اختر ملف Excel</label>
                                                    <input type="file" class="form-control" id="products-file" name="file" accept=".csv" required>
                                                    <div class="form-text">الملفات المدعومة: .csv فقط (حد أقصى 10 ميجا)</div>
                                                </div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ti ti-upload me-1"></i>
                                                    استيراد المنتجات
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-label-info">
                                        <div class="card-body text-center">
                                            <i class="ti ti-download fs-2 text-info mb-2"></i>
                                            <h6>تحميل القالب</h6>
                                            <p class="small">حمل قالب Excel للمنتجات</p>
                                            <a href="{{ route('imports.template', 'products') }}" class="btn btn-info btn-sm">
                                                <i class="ti ti-download me-1"></i>
                                                تحميل القالب
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6 class="text-primary">الأعمدة المطلوبة:</h6>
                                            <ul class="list-unstyled small">
                                                <li><strong>name:</strong> اسم المنتج (مطلوب)</li>
                                                <li><strong>code:</strong> كود المنتج (مطلوب)</li>
                                                <li><strong>category_name:</strong> اسم القسم</li>
                                                <li><strong>sub_category_name:</strong> اسم القسم الفرعي</li>
                                                <li><strong>unit_name:</strong> اسم الوحدة</li>
                                                <li><strong>stock:</strong> الكمية</li>
                                                <li><strong>price:</strong> السعر</li>
                                                <li><strong>min_stock:</strong> الحد الأدنى</li>
                                                <li><strong>max_stock:</strong> الحد الأقصى</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Categories Import -->
                        <div class="tab-pane fade" id="categories" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h6 class="mb-0">استيراد الأقسام</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('imports.categories') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="categories-file" class="form-label">اختر ملف Excel</label>
                                                    <input type="file" class="form-control" id="categories-file" name="file" accept=".xlsx,.xls,.csv" required>
                                                    <div class="form-text">الملفات المدعومة: .xlsx, .xls, .csv (حد أقصى 2 ميجا)</div>
                                                </div>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="ti ti-upload me-1"></i>
                                                    استيراد الأقسام
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-label-success">
                                        <div class="card-body text-center">
                                            <i class="ti ti-download fs-2 text-success mb-2"></i>
                                            <h6>تحميل القالب</h6>
                                            <p class="small">حمل قالب Excel للأقسام</p>
                                            <a href="{{ route('imports.template', 'categories') }}" class="btn btn-success btn-sm">
                                                <i class="ti ti-download me-1"></i>
                                                تحميل القالب
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sub Categories Import -->
                        <div class="tab-pane fade" id="sub-categories" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h6 class="mb-0">استيراد الأقسام الفرعية</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('imports.sub-categories') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="sub-categories-file" class="form-label">اختر ملف Excel</label>
                                                    <input type="file" class="form-control" id="sub-categories-file" name="file" accept=".xlsx,.xls,.csv" required>
                                                    <div class="form-text">الملفات المدعومة: .xlsx, .xls, .csv (حد أقصى 2 ميجا)</div>
                                                </div>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="ti ti-upload me-1"></i>
                                                    استيراد الأقسام الفرعية
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-label-warning">
                                        <div class="card-body text-center">
                                            <i class="ti ti-download fs-2 text-warning mb-2"></i>
                                            <h6>تحميل القالب</h6>
                                            <p class="small">حمل قالب Excel للأقسام الفرعية</p>
                                            <a href="{{ route('imports.template', 'sub_categories') }}" class="btn btn-warning btn-sm">
                                                <i class="ti ti-download me-1"></i>
                                                تحميل القالب
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6 class="text-warning">ملاحظة مهمة:</h6>
                                            <p class="small">يجب أن يكون القسم الرئيسي موجود مسبقاً، أو سيتم إنشاؤه تلقائياً.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Units Import -->
                        <div class="tab-pane fade" id="units" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h6 class="mb-0">استيراد الوحدات</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('imports.units') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="units-file" class="form-label">اختر ملف Excel</label>
                                                    <input type="file" class="form-control" id="units-file" name="file" accept=".xlsx,.xls,.csv" required>
                                                    <div class="form-text">الملفات المدعومة: .xlsx, .xls, .csv (حد أقصى 2 ميجا)</div>
                                                </div>
                                                <button type="submit" class="btn btn-secondary">
                                                    <i class="ti ti-upload me-1"></i>
                                                    استيراد الوحدات
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-label-secondary">
                                        <div class="card-body text-center">
                                            <i class="ti ti-download fs-2 text-secondary mb-2"></i>
                                            <h6>تحميل القالب</h6>
                                            <p class="small">حمل قالب Excel للوحدات</p>
                                            <a href="{{ route('imports.template', 'units') }}" class="btn btn-secondary btn-sm">
                                                <i class="ti ti-download me-1"></i>
                                                تحميل القالب
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Instructions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">تعليمات الاستيراد</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">خطوات الاستيراد:</h6>
                            <ol class="small">
                                <li>حمل القالب المناسب لنوع البيانات</li>
                                <li>املأ البيانات في القالب</li>
                                <li>احفظ الملف بصيغة Excel</li>
                                <li>ارفع الملف واضغط استيراد</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-warning">ملاحظات مهمة:</h6>
                            <ul class="small">
                                <li>استخدم أسماء الأقسام والوحدات بدلاً من الأرقام</li>
                                <li>إذا لم يوجد قسم أو وحدة، سيتم إنشاؤها تلقائياً</li>
                                <li>الأعمدة المطلوبة يجب ملؤها</li>
                                <li>تأكد من صحة البيانات قبل الاستيراد</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add loading states to form submissions
    const forms = document.querySelectorAll('form[enctype="multipart/form-data"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader-2 ti-spin me-1"></i>جاري الاستيراد...';
            
            // Re-enable after 30 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 30000);
        });
    });
});
</script>
@endsection
