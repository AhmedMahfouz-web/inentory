@extends('layouts.dashboard')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">تعديل الصنف</h5>
        <form class="card-body" method="post" action="{{ route('update product', $product->id) }}">
            @csrf <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="name">اسم الصنف</label>
                    <input type="text" id="name" value="{{ $product->name }}" name="name" class="form-control"
                        placeholder="اسم الصنف" />
                </div>
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="code">الكود</label>
                    <input type="text" id="code" disabled value="{{ $product->code }}" class="form-control"
                        placeholder="الكود" />
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">التصنيف</label>
                    <select id="select2Basic" class="select2 form-select form-select" name="category"
                        data-allow-clear="true">
                        @foreach ($categories as $category)
                            <option {{ $product->category_id == $category->id ? 'selected' : '' }}
                                value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">الوحدة</label>
                    <select id="select2Basic" class="select2 form-select form-select" name="unit"
                        data-allow-clear="true">
                        @foreach ($units as $unit)
                            <option {{ $product->unit_id == $unit->id ? 'selected' : '' }} value="{{ $unit->id }}">
                                {{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="min_stock">اقل كمية</label>
                    <input type="text" id="min_stock" value="{{ $product->min_stock }}" name="min_stock"
                        class="form-control" placeholder="اقل كمية" />
                </div>
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="max_stock">اقصي كمية</label>
                    <input type="text" id="max_stock" value="{{ $product->max_stock }}" name="max_stock"
                        class="form-control" placeholder="اقصي كمية" />
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="price">السعر</label>
                    <input type="text" id="price" value="{{ $product->price }}" name="price" class="form-control"
                        placeholder="السعر" />
                </div>
            </div>
            <div class="mt-5">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">حفظ</button>
                <a href="{{ route('show units') }}" class="btn btn-label-secondary">الغاء</a>
            </div>
    </div>
    </form>
    </div>
@endsection

@section('js')
    <script src="{{ asset('js/forms-selects.js') }}"></script>
@endsection
