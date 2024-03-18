{{-- confirm.blade.php --}}
@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
    use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    use Botble\SaleWarehouse\Models\SaleWarehouseChild;
    use Botble\SaleWarehouse\Models\SaleProduct;
    use Botble\ProductQrcode\Enums\QRStatusEnum;
    use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
    use Botble\Media\Facades\RvMedia;
    use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;

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

            <div class="w-75 p-3 mx-auto" id="main-order-content" style="width: 750px;">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <form method="POST" id="submit_form"
                                    action="{{ route('sale-issue.confirm', $productIssue->id) }}" class="form-2">
                                    <div id="qr_ids_wrapper">
                                        @php
                                            $groupedProducts = [];
                                            $batches = [];
                                            $groupedProducts = $productIssue->actualQrCode
                                                ->filter(function ($detail) {
                                                    return $detail->batch_id > 0;
                                                })
                                                ->groupBy('batch_id');
                                            $groupedOdd = $productIssue->actualQrCode->filter(function ($detail) {
                                                return $detail->batch_id == 0;
                                            });

                                        @endphp
                                        @foreach ($groupedProducts as $batch_id => $products)
                                            @php
                                                $isBatch = $products->where('is_batch', 1)->isNotEmpty();
                                            @endphp
                                            <input type="hidden" name="batch_ids[]"
                                                value="{{ $products?->first()?->batch?->id }}" />
                                        @endforeach
                                        @foreach ($groupedOdd as $odd)
                                            <input type="hidden" name="qr_ids[]" value="{{ $odd->qrcode_id }}" />
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="product_issue" id="product_issue"
                                        value="{{ $productIssue->id }}" />
                                    <div class="wrapper-content">
                                        <div class="pd-all-20">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h2 class="title">Thông tin phiếu xuất kho
                                                        {{ BaseHelper::clean(get_proposal_issue_product_code($productIssue->issue_code)) }}
                                                    </h2>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info-group">
                                                                <label>Kho xuất:</label>
                                                                <strong>{{ $productIssue->warehouse_name }} -
                                                                    {{ $productIssue->warehouseIssue->saleWarehouse->name }}</strong>
                                                            </div>
                                                            <div class="info-group">
                                                                <label>Kho nhận:</label>
                                                                <strong>
                                                                    {{ $productIssue->proposal->is_warehouse == 'tour' ? 'Xuất đi giải' : '' }}
                                                                </strong>
                                                            </div>
                                                            <div class="info-group">
                                                                <label>Mã đơn hàng:</label>
                                                                <strong>{{ $productIssue->general_order_code ?: '—' }}</strong>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div>
                                                                Người đề xuất:
                                                                <strong>{{ $productIssue->invoice_issuer_name }}</strong>
                                                            </div>
                                                            <div>
                                                                Mục đích: <strong>{{ $productIssue->title }}</strong>
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
                                        @php
                                            $totalQty = 0;
                                            $products = [];
                                            $hasBatchProducts = false;
                                            $hasNonBatchProducts = false;
                                        @endphp

                                        @foreach ($productIssue->productIssueDetail as $orderProduct)
                                            @if ($orderProduct->is_batch == 1)
                                                @php
                                                    $hasBatchProducts = true;
                                                @endphp
                                            @endif
                                            @if ($orderProduct->is_batch == 0)
                                                @php
                                                    $hasNonBatchProducts = true;
                                                @endphp
                                            @endif
                                        @endforeach
                                        <div class="card-body pd-all-20 p-none-t border-top-title-main">
                                            @csrf
                                            <div id="table-wrapper" class="table-wrap">
                                                @if ($hasBatchProducts)
                                                    <div class="col-md-12">
                                                        <h3>Xuất theo lô</h3>
                                                        <table
                                                            class="table-order table-divided table-vcenter card-table col-12"
                                                            id="table-content">
                                                            <tbody>

                                                                @foreach ($productIssue->productIssueDetail as $orderProduct)
                                                                    @if ($orderProduct->is_batch == 1)
                                                                        @php
                                                                            $products[] = $orderProduct;
                                                                            $quantity =
                                                                                $orderProduct->is_batch == 1
                                                                                    ? ProductBatch::where([
                                                                                        'warehouse_type' =>
                                                                                            Warehouse::class,
                                                                                        'warehouse_id' =>
                                                                                            $productIssue->warehouse_issue_id,
                                                                                        'product_parent_id' =>
                                                                                            $orderProduct->product_id,
                                                                                        'status' =>
                                                                                            ProductBatchStatusEnum::INSTOCK,
                                                                                    ])->count()
                                                                                    : QuantityProductInStock::where([
                                                                                        'product_id' =>
                                                                                            $orderProduct->product_id,
                                                                                        'stock_id' =>
                                                                                            $productIssue->warehouse_id,
                                                                                    ])?->first()?->quantity;
                                                                        @endphp
                                                                        <tr class="item__product">
                                                                            <input type="hidden" id="product_id"
                                                                                name="product[{{ $orderProduct->id }}][product_id]"
                                                                                value="{{ $orderProduct->product_id }}" />
                                                                            <input type="hidden" id="warehouse_id"
                                                                                value="{{ $productIssue->warehouse_id }}" />
                                                                            <input type="hidden" id="orderProductId"
                                                                                value="{{ $orderProduct->id }}" />
                                                                            <td class="vertical-align-t" width="20%"
                                                                                style="margin:20px">
                                                                                <div class="wrap-img">
                                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                                        width="60px" height="60px"
                                                                                        src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                        alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                                </div>
                                                                            </td>
                                                                            <td class="pl5 p-r5 " width = "30%">
                                                                                {{ $orderProduct->product_name }}
                                                                                <input type="text" class="widget__price"
                                                                                    name="product[{{ $orderProduct->id }}][attr]"
                                                                                    value="{{ $orderProduct->attribute }}"
                                                                                    hidden="">
                                                                            </td>

                                                                            <td class="pl5 p-r5 text-center" width="30%">
                                                                                <span style="white-space: nowrap;">Lô:
                                                                                    {{ $orderProduct?->batch?->batch_code }}</span>
                                                                                <input type="text" class="widget__batch"
                                                                                    name="product[{{ $orderProduct->id }}][sku]"
                                                                                    value="{{ $orderProduct->sku }}"
                                                                                    hidden>
                                                                            </td>
                                                                            <td class="pl5 p-r5  text-end" width="20%">
                                                                                <span style="white-space: nowrap;">Số
                                                                                    lượng:
                                                                                    {{ $orderProduct->quantity }} </span>
                                                                            </td>
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                                @php
                                                    $submitDisabe = true;
                                                @endphp
                                                @if ($hasNonBatchProducts)
                                                    <div class="col-md-12">
                                                        {{-- <h3 style="margin: 20px 0 0 0">Xuất lẻ</h3> --}}
                                                        <table
                                                            class="table-order table-divided table-vcenter card-table col-12"
                                                            width="100%" id="table-content">
                                                            <tbody>
                                                                @php
                                                                    $submitDisabe = false;
                                                                    $total = 0;
                                                                @endphp
                                                                @foreach ($productIssue->productIssueDetail as $orderProduct)
                                                                    @if ($orderProduct->is_batch == 0)
                                                                        @php
                                                                            $quantity = SaleProduct::where([
                                                                                'product_id' =>
                                                                                    $orderProduct->product_id,
                                                                                'warehouse_id' =>
                                                                                    $productIssue->warehouse_issue_id,
                                                                            ])?->first()?->quantity;
                                                                        @endphp
                                                                        <tr class="item__product">
                                                                            <td class=" vertical-align-t" width="10%"
                                                                                style="margin:20px">
                                                                                <input type="hidden" id="product_id"
                                                                                    name="product[{{ $orderProduct->id }}][product_id]"
                                                                                    value="{{ $orderProduct->product_id }}" />
                                                                                <input type="hidden" id="warehouse_id"
                                                                                    value="{{ $productIssue->warehouse_id }}" />
                                                                                <input type="hidden" id="orderProductId"
                                                                                    value="{{ $orderProduct->id }}" />
                                                                                <div class="wrap-img">
                                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                                        width="60px" height="60px"
                                                                                        src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                        alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                                </div>
                                                                            </td>
                                                                            <td class="pl5 p-r5" width = "20%">
                                                                                {{ $orderProduct->product->name }}
                                                                                <br>
                                                                                @foreach ($orderProduct->product($orderProduct->product_id)->first()?->variationProductAttributes as $attribute)
                                                                                    @if ($attribute?->color)
                                                                                        {{ 'Màu: ' . $attribute->title }}
                                                                                        @php
                                                                                            $orderProduct->color =
                                                                                                $attribute->title;
                                                                                        @endphp
                                                                                    @endif
                                                                                @endforeach

                                                                                @foreach ($orderProduct->product($orderProduct->product_id)->first()?->variationProductAttributes as $attribute)
                                                                                    @if (!$attribute->color)
                                                                                        {{ 'Size: ' . $attribute->title }}
                                                                                        @php
                                                                                            $orderProduct->size =
                                                                                                $attribute->title;
                                                                                        @endphp
                                                                                    @endif
                                                                                @endforeach
                                                                                <input type="text"
                                                                                    class="widget__price"
                                                                                    name="product[{{ $orderProduct->id }}][attr]"
                                                                                    value="{{ $orderProduct->attribute }}"
                                                                                    hidden="">
                                                                            </td>
                                                                            <td class="pl5 p-r5 text-center  "
                                                                                width = "15%">
                                                                                Mã : {{ $orderProduct->product->sku }}
                                                                            </td>
                                                                            <input type="text" class="widget__batch"
                                                                                name="product[{{ $orderProduct->id }}][sku]"
                                                                                value="{{ $orderProduct->sku }}" hidden>
                                                                            <td class="pl5 p-r5  text-center"
                                                                                width="20%">
                                                                                <span style="white-space: nowrap;">Tồn kho:
                                                                                    {{ $quantity }}
                                                                                    sản phẩm</span>
                                                                            </td>
                                                                            <td class="pl5 p-r5  text-center"
                                                                                width="20%">
                                                                                <span style="white-space: nowrap;">Đề
                                                                                    xuất:
                                                                                    {{ $orderProduct->quantity }}
                                                                                    sản phẩm</span>
                                                                            </td>
                                                                            @php
                                                                                $total += $orderProduct->quantity;
                                                                            @endphp
                                                                            <td class="pl5 p-r5" width="20%">
                                                                                <span style="white-space: nowrap;">Đã quét:
                                                                                    <span
                                                                                        id="remain_{{ $orderProduct->product_id }}">
                                                                                        {{ $orderProduct->quantity_scan ?: 0 }}
                                                                                    </span></span>
                                                                            </td>

                                                                            </td>
                                                                        </tr>
                                                                        @php

                                                                            $orderProduct->quantity =
                                                                                $orderProduct->quantity -
                                                                                $orderProduct->quantity_scan;
                                                                            if ($orderProduct->quantity > 0) {
                                                                                $submitDisabe = true;
                                                                            }

                                                                            $products[] = $orderProduct;
                                                                        @endphp
                                                                    @endif
                                                                @endforeach
                                                                <tr>
                                                                    <td colspan="6" class="text-end h5">
                                                                        <h3>
                                                                            <strong>Tổng số lượng xuất: </strong> <span
                                                                                class="check__highlight">{{ $total }}</span>
                                                                        </h3>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif


                                            </div>

                                            @push('footer')
                                                <script>
                                                    window.warehouse_id = @json($productIssue->warehouse_id);
                                                    window.warehouse_type = @json(Warehouse::class);
                                                    window.products = @json($products);
                                                </script>
                                            @endpush
                                        </div>

                                        <div class="card-footer pd-all-20 p-none-t">
                                            <div class="mt10">
                                                <div class="row">
                                                    <div class="col-8">
                                                        <div class="button-container m-2">
                                                            <button class="btn btn-primary" type="submit"
                                                                id="submit_btn" {{ $submitDisabe ? 'disabled' : '' }}>Xác
                                                                nhận xuất</button>
                                                            <span class="tooltip-text">Quét QR trước khi xác nhận</span>
                                                        </div>
                                                        @php
                                                            echo Auth::user()->hasPermission([
                                                                'sale-issue.denied',
                                                            ]) &&
                                                            $productIssue->status == ProductIssueStatusEnum::PENDING
                                                                ? '<button class="btn btn-danger" type="button" id="denyButton">Từ chối</button>'
                                                                : '';
                                                        @endphp
                                                    </div>

                                                    <div class="d-flex justify-content-end align-items-center col 4">
                                                        <button type="button" class="btn btn-primary d-inline m-2"
                                                            data-bs-toggle="modal"data-type="batch"
                                                            data-bs-target="#warehouseIssueModal" id="open_scan_modal">
                                                            <span class="me-2">
                                                                <i class="fa-solid fa-qrcode"></i>
                                                            </span>
                                                            Quét QR
                                                        </button>
                                                        <button type="button" class="btn btn-success d-inline"
                                                            data-bs-toggle="modal" data-type="batch"
                                                            data-bs-target="#qrList" id="open_scan_modal">
                                                            <span class="me-2">
                                                                <i class="fa-solid fa-list"></i>
                                                            </span>
                                                            Danh sách quét
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

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

                    <h3>Từ chối xuất kho {{ $productIssue->proposal_code }} </h3>

                </div>
                <div class="modal-body">
                    <form id="denyForm" method="post" action="{{ route('sale-issue.denied', $productIssue->id) }}">
                        @csrf
                        <label for="denyReason">Lý do từ chối:</label>
                        <textarea class="form-control required" id="denyReason" name="denyReason" rows="3"></textarea>
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

    @include('plugins/qr-scan::scan-warehouse-issue', [
        'warehouse_id' => $productIssue->warehouse_issue_id,
        'warehouse_type' => SaleWarehouseChild::class,
        'warehouse_receipt_type' => $productIssue->warehouse_type,
        'data' => $products,
        'qrcode' => $productIssue->actualQrCode,
        'groupedOdd' => $groupedOdd,
        'url_confirm' => 'sale-issue.createBatchIssue'
    ])
    <script>
        $("#denyButton").on("click", function() {
            $("#denyModal").modal("show");
        });
        $("#denySubmit").on("click", function(e) {
            e.preventDefault();
            var denyReason = $("#denyReason").val();
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
        document.getElementById("submit_form").addEventListener("submit", function() {
            document.getElementById("submit_btn").disabled = true;
        });
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
