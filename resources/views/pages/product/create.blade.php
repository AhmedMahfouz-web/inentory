@extends('layouts.dashboard')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">اضافة صنف جديد</h5>
        <form class="card-body" method="post" action="{{ route('store product') }}">
            @csrf
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="name">اسم الصنف</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="اسم الصنف" />
                </div>
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="code">الكود</label>
                    <input type="text" id="code" name="code" class="form-control" placeholder="الكود" />
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">التصنيف</label>
                    <select class="select2 select2Basic form-select form-select" name="category" data-allow-clear="true">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">الوحدة</label>
                    <select class="select2 select2Basic form-select form-select" name="unit" data-allow-clear="true">
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="name">اقل كمية</label>
                    <input type="text" id="min_stock" name="min_stock" class="form-control" placeholder="اقل كمية" />
                </div>
                <div class="col-md-6">
                    <label class="mb-2 text-light fw-semibold" for="code">اقصي كمية</label>
                    <input type="text" id="max_stock" name="max_stock" class="form-control" placeholder="اقصي كمية" />
                </div>
            </div>
            <div class="mt-5">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">حفظ</button>
                <a href="{{ route('show categories') }}" class="btn btn-label-secondary">الغاء</a>
            </div>
    </div>
    </form>
    </div>
@endsection

@section('js')
    <script src="{{ asset('js/forms-selects.js') }}"></script>
@endsection
