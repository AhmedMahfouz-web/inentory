<!DOCTYPE html>
<html>

<head>
</head>

<body>

    <h1>Hello</h1>
    <h2>Hello</h2>
    <h3>Hello</h3>
    <h4>Hello</h4>
    {{-- <h5>{{ $order_id }}</h5> --}}
    @php
        $url = route('exchange product');
        $urlWithoutProtocol = preg_replace('#^https?://#', '', $url);
    @endphp
    <script>
        window.load(function() {

            // إعادة توجيه الصفحة الحالية إلى صفحة الشكر
            window.location.href = {{ $urlWithoutProtocol }};
        })
    </script>
</body>

</html>
