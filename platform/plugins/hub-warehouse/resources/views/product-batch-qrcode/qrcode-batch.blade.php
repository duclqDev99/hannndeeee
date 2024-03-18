@php
    use Carbon\Carbon;
    $logoPath = 'images/logo-handee.png';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: 'Roboto', sans-serif !important;
            width: 65mm;
            height:30mm;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            flex-direction: column;
            width: 100%;
            padding: 0 10px;
        }

        ul{
            list-style:none;
            text-align: center;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .item {
            height: calc(30mm - 17px);
            width: 100%;
            padding-top: 14px;
        }

        .item .card{
            display: flex;
            align-items:center;
            justify-content: space-between;
            border: 3px solid #000;
            border-radius: 5px;
            padding: 10px;
        }


        @media print {
            body {
                width: 65mm;
                height: 30mm;
            }

            @page {
                size: 65mm 30mm;
                margin: 0px;
            }
        }
</style>
</head>
<body>
    <div class="container" id="prin_qr_code_theme">
        @if(is_array($proBatch))
            @foreach($proBatch as $item)
                <div class="item">
                    <div class="card">
                        <div style="text-align: center ">
                            <img width="65px" height="65px" src="data:image/png;base64, {{$item->base_code_64}}">
                        </div>
                        <div>
                            <ul style="text-align: center; list-style:none; margin: 0; padding: 0;">
                                <li id="test" style="text-align: center; margin: 0 auto;"><strong>{{$item->batch->batch_code}}</strong></li>
                                <li  style="text-align: center; margin: 0 auto;">Sản phẩm: {{$item->batch->product->name}}</li>
                                <li  style="text-align: center; margin: 0 auto;">SL sản phẩm: {{$item->batch->start_qty}}</li>
                                <li  style="text-align: center; margin: 0 auto;">Ngày tạo: {{Carbon::parse($item->batch->created_at)->format('d/m/Y')}}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
        <div class="item" style="flex-wrap:nowrap;">
            <div class="card">
                <div style="text-align: center;">
                    <img width="65px" height="65px" src="data:image/png;base64, {{$proBatch->base_code_64}}">
                </div>
                <div>
                    <ul style="text-align: center; list-style:none; margin: 0; padding: 0;">
                        <li id="test" style="text-align: center; margin: 0 auto;"><strong>{{$proBatch->batch->batch_code}}</strong></li>
                        <li  style="text-align: center; margin: 0 auto;">SL sản phẩm: {{$proBatch->batch->start_qty}}</li>
                        <li  style="text-align: center; margin: 0 auto;">Ngày tạo: {{Carbon::parse($proBatch->batch->created_at)->format('d/m/Y')}}</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>
</body>
</html>
