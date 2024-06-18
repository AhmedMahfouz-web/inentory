@extends('layouts.dashboard')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">بداية المدة للاصناف</h5>
        <hr class="my-3 mx-n4">
        <form class="card-body" method="post" action="{{ route('store start', $branch_id) }}">
            @csrf
            @foreach ($products as $product)
                <div class="row g-3 mt-3">
                    <label class="col-2 col-form-label text-light fw-semibold"
                        for="start">{{ $product->product->name }}</label>

                    <div class="col-2">
                        <input type="number" id="start"
                            value="{{ empty($product->start[0]->qty) ? 0 : $product->start[0]->qty }}" step=".01"
                            name="start[]" class="form-control" placeholder="بداية المدة" />
                        <input type="hidden" id="product_id" value="{{ $product->id }}" name="product_id[]"
                            class="form-control" />
                    </div>
                </div>
            @endforeach
            <hr class="my-3 mx-n4">
            <button type="submit" class="btn btn-primary mt-4 float-end mx-5">حفظ</button>
        </form>
    </div>
@endsection

@section('js')
    <script src="{{ asset('js/forms-selects.js') }}"></script>
@endsection
