@extends('layouts.dashboard')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">اضافة قسم جديدة</h5>
        <form class="card-body" method="post" action="{{ route('store category') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">اسم القسم</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="اسم القسم" />
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="code">الكود</label>
                    <input type="text" id="code" name="code" class="form-control" placeholder="الكود" />
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
