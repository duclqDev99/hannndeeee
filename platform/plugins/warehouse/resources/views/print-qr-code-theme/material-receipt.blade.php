@php
    use Carbon\Carbon;
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
    use Intervention\Image\Facades\Image;
    use Botble\Media\Facades\RvMedia;

    $logoPath = 'images/logo-handee.png';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,600;0,700;1,100;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif !important;
            width: 100mm;
            height: 58mm;
            margin: 0;
            padding: 0;
        }


        .header {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .details {
            margin-bottom: 10px;
        }

        .item {
            height: 48mm;
            display: flex;
            align-items:center;
            justify-content: space-between;
            padding: 10px;
            margin: 10px;
            padding-top: 10px;
            border: 3px solid #000;
            border-radius:5px ;
        }
        .item:not(:first-child) {
            margin-top: 10px;
        }

        .total {
            font-weight: bold;
            margin-top: 10px;
        }
        .text-title {
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            display: -webkit-box;
            text-overflow: ellipsis;
        }
        @media print {
            body {
                width: 100mm;
                height: 55mm;
            }
        }
    </style>
</head>
<body>
    @foreach ($batchCodeList as $batchCode)
        <div class="item" style="display: flex; flex-wrap:nowrap ">
            <div style="text-align: center ">
                <img src="data:image/png;base64, {{$batchCode->qr_code_base64}}">
            </div>
            <div>
                <ul style="text-align: center; list-style:none; margin: 0; padding: 0">
                    <li id="test" style="text-align: center; margin: 0 auto;" ><strong style="margin: 0 auto">{{$receipt->warehouse_name}}</strong></li>
                    <br/>
                    <li class="text-title"><strong>{{$batchCode->material->name}}</strong></li>
                    <li>SL ban đầu : {{$batchCode->start_qty}} {{$batchCode->material->unit}}</li>
                    <li>Ngày nhập: {{Carbon::parse($batchCode->created_at)->format('d-m-Y')}}</li>
                </ul>
            </div>
        </div>
    @endforeach
</body>
</html>
