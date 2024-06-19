<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Printable Page</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .header,
        .footer {
            width: 100%;
            text-align: center;
            position: fixed;
        }

        .header {
            top: 0;
        }

        .footer {
            bottom: 0;
        }

        .content {
            margin-top: 50mm;
            /* Adjust according to your header height */
            margin-bottom: 30mm;
            /* Adjust according to your footer height */
            page-break-before: auto;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Your Header Content</h1>
    </div>

    <div class="footer">
        <p>Your Footer Content</p>
    </div>

    <div class="content">
        @foreach ($order->product_added as $product)
            <div class="item">
                <p>{{ $product->product->names }}</p>
            </div>
            <div class="page-break"></div>
        @endforeach
    </div>
</body>

</html>
