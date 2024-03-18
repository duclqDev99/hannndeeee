@php
    use Carbon\Carbon;
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
    use Intervention\Image\Facades\Image;
    use Botble\Media\Facades\RvMedia;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: 'Roboto', sans-serif !important;
            width: 30mm;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            padding: 10px;
        }


        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 10px;
            border: 3px solid #000;
            border-radius:5px ;
        }

    </style>
</head>
<body>
    <div class="container" id="prin_qr_code_theme">
            @foreach ($batchCodeList as $batchCode)
                @php
                    $logoPath = 'images/logo-handee.png';
                    $qrCodeWithLogo = QrCode::size(30)
                        ->format('png')
                        ->merge($logoPath, 0.3, true)
                        ->errorCorrection('H')
                        ->generate($batchCode->qr_code);
                @endphp
                <div class="item" style="display: flex; flex-wrap:nowrap ">
                    <div style="text-align: center;">
                        <img src="data:image/png;base64, {!! base64_encode($qrCodeWithLogo) !!} ">
                    </div>
                </div>
        @endforeach
    </div>
</body>
</html>
