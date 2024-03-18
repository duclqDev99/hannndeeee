@php
    $quantity = $qrcode['quantity_product'];
    $chunkSize = 200;
    $totalChunks = ceil($quantity / $chunkSize);
    $checkPermission = Auth::guard()->user();
    $checkIssetQrcode = $qrcode->quantity_product > $qrcode->quantity_cancell ? true : false;
@endphp

<div class="btn-list">
    @if (!$checkSuperPrint && $export  && $checkIssetQrcode)
        <button
            id = "print-qrcode"
            type="button"
            class="btn btn-icon btn-success btn-sm"
            data-id = "{{$qrcode->product_id}}"
            data-target="{{$getRoute}}"
            data-url-confirm="{{$printTimesCountRoute}}"
        >
            <i class="fa fa-qrcode"></i>
        </button>
    @endif
    @if ($checkPermission->hasPermission('product-qrcode.detail'))
        <a
            href= {{$routeDetail}}
            type="button"
            class="btn btn-icon btn-warning btn-sm"
        >
            <i class="fa-solid fa-eye"></i>
        </a>
    @endif

</div>

<div id="modalPrint"></div>


