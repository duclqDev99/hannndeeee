{{-- confirm.blade.php --}}
@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
    use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    use Botble\HubWarehouse\Models\Warehouse;
    use Botble\HubWarehouse\Models\QuantityProductInStock;
    use Botble\ProductQrcode\Enums\QRStatusEnum;
    use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
    use Botble\Media\Facades\RvMedia;

@endphp
@section('content')
    <style>
        .flexbox-grid-default {
            position: relative;
        }

        .card-notify-year {
            position: absolute;
            right: -10px;
            top: -15px;
            background: #ff4444;
            text-align: center;
            color: #fff;
            font-size: 14px;
            padding: 5px;
            padding-left: 30px;
            clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 10% 50%, 0% 0%);
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .collapsing {
            -webkit-transition: none;
            transition: none;
            display: none;
        }

        /**
                                                                                                                                                                                                                                                                                 * Extracted from: SweetAlert
                                                                                                                                                                                                                                                                                 * Modified by: Istiak Tridip
                                                                                                                                                                                                                                                                                 */
        @keyframes sparkle {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.4);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .check-icon-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .fa-check {
            font-size: 1.5em;
            color: #198754;
            animation: sparkle 0.5s 1;
        }
    </style>

    {{-- new --}}
    <div>
        <div class="row">
            <div class="col-12 mb-3">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary d-inline" data-bs-toggle="modal"data-type="batch"
                        data-bs-target="#warehouseIssueModal" id="open_scan_modal">
                        <span class="me-2">
                            <i class="fa-solid fa-qrcode"></i>
                        </span>
                        Quét QR
                    </button>
                </div>
            </div>
            <div class="w-75 p-3 mx-auto" id="main-order-content" style="width: 750px;">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <form method="POST" action="{{ route('hub-receipt.confirmProduct', $receipt) }}"
                                    class="form-2">
                                    <div id="qr_ids_wrapper"></div>
                                    <input type="hidden" name="product_issue" value="{{ $receipt->id }}" />
                                    <div class="wrapper-content">
                                        <div class="pd-all-20">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h2 class="title">Thông tin phiếu nhâp kho
                                                        {{ BaseHelper::clean(get_proposal_receipt_product_code($receipt->receipt_code)) }}
                                                    </h2>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info-group">
                                                                <label>Kho xuất:</label>
                                                                <strong> @php
                                                                    $isSameWarehouse = $receipt->warehouse_receipt_id == $receipt->warehouse_id;
                                                                    $isWarehouseType = $receipt->warehouse_type == Warehouse::class;
                                                                @endphp

                                                                    @if ($isSameWarehouse && $isWarehouseType)
                                                                        Nhập hàng
                                                                        tồn
                                                                    @else
                                                                        {{ $receipt->warehouse->hub?->name
                                                                            ? $receipt->warehouse->name . ' - ' . $receipt->warehouse->hub->name
                                                                            : $receipt->warehouse->name }}
                                                                    @endif
                                                                </strong>
                                                            </div>
                                                            <div class="info-group">
                                                                <label>Kho nhận:</label>
                                                                <strong>{{ $receipt->warehouse->name }} -
                                                                    {{ $receipt->warehouse?->hub?->name ?:
                                                                        ($receipt->warehouse?->agent?->name ?:
                                                                            ($receipt->warehouse?->showroom?->name ?:
                                                                                '')) }}</strong>
                                                            </div>
                                                            <div class="info-group">
                                                                <label>Mã đơn hàng:</label>
                                                                <strong>{{ $receipt->general_order_code ?: '—' }}</strong>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div>
                                                                Người đề xuất:
                                                                <strong>{{ $receipt->invoice_issuer_name }}</strong>
                                                            </div>
                                                            <div>
                                                                Mục đích: <strong>{{ $receipt->title }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pd-all-20" style="margin: 10px">
                                            <label for="images[]" class="form-label">Hình ảnh đính kèm</label>
                                            <div class="gallery-images-wrapper list-images form-fieldset">
                                                <div class="images-wrapper mb-2">
                                                    <div data-bb-toggle="gallery-add"
                                                        class="text-center cursor-pointer default-placeholder-gallery-image"
                                                        data-name="images[]">
                                                        <div class="mb-3">
                                                            @php
                                                                echo BaseHelper::renderIcon('ti ti-photo-plus');
                                                            @endphp
                                                        </div>
                                                        <p class="mb-0 text-body">
                                                            Chọn hình ảnh đính kèm
                                                        </p>
                                                    </div>
                                                    <input name="images[]" type="hidden">
                                                    <div class="row w-100 list-gallery-media-images ui-sortable"
                                                        data-name="images[]" data-allow-thumb="1">

                                                    </div>
                                                </div>
                                                <div style="display: none;" class="footer-action">
                                                    <a data-bb-toggle="gallery-add" class="me-2 cursor-pointer">Thêm</a>
                                                    <button class="text-danger cursor-pointer btn-link"
                                                        data-bb-toggle="gallery-reset">
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="card-body pd-all-20 p-none-t border-top-title-main">
                                            @csrf
                                            <div id="table-wrapper" class="table-wrap">

                                                <div class="col-md-12">

                                                    <table class="table-order table-divided table-vcenter card-table col-12"
                                                        width="100%" id="table-content">
                                                        <tbody>
                                                            @foreach ($receipt->receiptDetail as $orderProduct)
                                                                @if ($orderProduct->is_batch == 0)
                                                                    @php
                                                                        $products[] = $orderProduct;
                                                                        $quantity = QuantityProductInStock::where(['product_id' => $orderProduct->product_id, 'stock_id' => $receipt->warehouse_id])?->first()?->quantity;
                                                                    @endphp
                                                                    <tr class="item__product">
                                                                        <td class=" vertical-align-t" width="20%"
                                                                            style="margin:20px">
                                                                            <input type="hidden" id="product_id"
                                                                                name="product[{{ $orderProduct->id }}][product_id]"
                                                                                value="{{ $orderProduct->product_id }}" />
                                                                            <input type="hidden" id="warehouse_id"
                                                                                value="{{ $receipt->warehouse_id }}" />
                                                                            <input type="hidden" id="orderProductId"
                                                                                value="{{ $orderProduct->id }}" />
                                                                            <div class="wrap-img">
                                                                                <img class="thumb-image thumb-image-cartorderlist"
                                                                                    width="60px" height="60px"
                                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                    alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                            </div>
                                                                        </td>
                                                                        <td class="pl5 p-r5" width = "30%">
                                                                            {{ $orderProduct->product_name }}

                                                                            Màu: {{ $orderProduct->color }}
                                                                            Size: {{ $orderProduct->size }}

                                                                            @php
                                                                                echo $orderProduct->is_batch === 0 ? ' <br> (Màu: ' . $orderProduct->color . '- Size: ' . $orderProduct->size . ')' : '';
                                                                            @endphp
                                                                            <input type="text" class="widget__price"
                                                                                name="product[{{ $orderProduct->id }}][attr]"
                                                                                value="{{ $orderProduct->attribute }}"
                                                                                hidden="">
                                                                        </td>

                                                                        <input type="text" class="widget__batch"
                                                                            name="product[{{ $orderProduct->id }}][sku]"
                                                                            value="{{ $orderProduct->sku }}" hidden>



                                                                        <td class="pl5 p-r5  text-center" width="20%">
                                                                            <span style="white-space: nowrap;">Đề
                                                                                xuất:
                                                                                {{ $orderProduct->quantity }}
                                                                                sản phẩm</span>
                                                                        </td>
                                                                        <td class="pl5 p-r5 text-center" width="20%">
                                                                            <span style="white-space: nowrap;">Đã nhập:
                                                                                {{ $quantity ?: 0 }} </span>
                                                                        </td>
                                                                        <td class="pl5 p-r5  text-end" width="20%">
                                                                            <span style="white-space: nowrap;">Còn lại:
                                                                                {{ $orderProduct->quantity }}
                                                                            </span>
                                                                        </td>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                        </tbody>
                                                    </table>
                                                </div>


                                            </div>

                                            @push('footer')
                                                <script>
                                                    window.warehouse_id = @json($receipt->warehouse_id);
                                                    window.warehouse_type = @json(Warehouse::class);
                                                    window.products = @json($products);
                                                </script>
                                            @endpush
                                        </div>

                                        <div class="card-footer pd-all-20 p-none-t">
                                            <div class="mt10">
                                                <div class="button-container">
                                                    <button class="btn btn-primary" type="submit" id="submit_btn"
                                                        disabled>Xác nhận nhập kho</button>
                                                    <span class="tooltip-text">Quét QR trước khi xác nhận</span>
                                                </div>
                                                @php
                                                    echo Auth::user()->hasPermission(['product-issue.denied'])
                                                        ? '<button class="btn btn-danger" type="button" id="denyButton">Từ
                                                    chối</button>'
                                                        : '';
                                                @endphp

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="denyModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-status bg-danger"></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body text-center py-4">
                    <div class="mb-2">
                        <span class="icon-tabler-wrapper icon-lg text-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M12 9v4"></path>
                                <path
                                    d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                </path>
                                <path d="M12 16h.01"></path>
                            </svg>


                        </span>
                    </div>

                    <h3>Từ chối nhập kho </h3>

                </div>
                <div class="modal-body">
                    <form id="denyForm"action="{{ route('hub-receipt.cancel', $receipt) }}" method="post">
                        @csrf
                        <label for="denyReason">Lý do từ chối:</label>
                        <textarea name="reasoon" class="form-control" id="reasoon" placeholder="Ghi rõ lý do" required></textarea>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-danger" id="denySubmit">Từ
                                chối</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- modal --}}
    @foreach ($products as $product)
    @endforeach
    @include('plugins/qr-scan::scan-warehouse-receipt', [
        'warehouse_id' => $receipt->warehouse_receipt_id,
        'warehouse_type' => Warehouse::class,
    ])
    <script>
        $("#denyButton").on("click", function() {
            $("#denyModal").modal("show");
        });
        $("#denySubmit").on("click", function(e) {
            e.preventDefault();
            var denyReason = $("#reasoon").val();
            if (denyReason.trim() === "") {
                alert("Vui lòng nhập lý do từ chối.");
                return;
            }
            $("#denyForm").submit();
        });
    </script>

@stop

<script>
    window.addEventListener('load', function() {
        const wp_content = document.querySelector('.ui-layout__item.receipt');
        const wp_receipt_goods = wp_content?.querySelector('.receipt-goods');

        if (wp_receipt_goods) {
            const trItem = wp_receipt_goods.querySelectorAll('table tbody tr:nth-child(odd)');
            const reasoon__receipt = wp_receipt_goods.querySelectorAll('table tbody tr:nth-child(even)');

            trItem?.forEach((element, index) => {
                const inputQuantity = element.querySelector('input.base__quantity');

                console.log(element);
                if (inputQuantity) {

                    reasoon__receipt[index].style.display = 'none';

                    let qty_default = inputQuantity.dataset.default;

                    inputQuantity?.addEventListener('keyup', function(event) {
                        event.preventDefault();

                        if (event.target.value == qty_default) {
                            reasoon__receipt[index].style.display = 'none';
                        } else {
                            reasoon__receipt[index].style.display = 'table-row';
                        }
                    })
                }
            });
        }
    })
</script>
<style>
    .button-container {
        position: relative;
        display: inline-block;
    }

    .tooltip-text {
        visibility: hidden;
        width: 200px;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 0;
        position: absolute;
        z-index: 1;
        bottom: 100%;
        left: 50%;
        margin-left: -100px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    /* Show the tooltip only when hovering over a disabled button */
    .button-container:hover button:disabled+.tooltip-text {
        visibility: visible;
        opacity: 1;
    }
</style>

@push('header')
    <meta name="apple-mobile-web-app-capable" content="yes">
@endpush





{{-- @push('footer')
    <script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
@endpush --}}
