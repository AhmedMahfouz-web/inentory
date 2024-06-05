@extends('layouts.dashboard')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">تعديل القسم</h5>
        <form class="card-body" method="post" action="{{ route('update sub_category', $category->id) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">اسم القسم</label>
                    <input type="text" id="name" value="{{ $category->name }}" name="name" class="form-control"
                        placeholder="اسم القسم" />
                </div>
                <div class="col-md-6">
                    <label for="select2Basic" class="mb-2 text-light fw-semibold">التصنيف</label>
                    <select id="select2Basic" class="select2 form-select form-select" name="category"
                        data-allow-clear="true">
                        @foreach ($parent_categories as $parent_category)
                            <option {{ $category->category_id == $parent_category->id ? 'selected' : '' }}
                                value="{{ $parent_category->id }}">{{ $parent_category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="code">كود القسم</label>
                    <input type="text" id="code" value="{{ $category->code }}" name="code" class="form-control"
                        placeholder="كود القسم" />
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
