{{-- confirm.blade.php --}}
@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
    use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    use Botble\Showroom\Models\ShowroomWarehouse;
    use Botble\Showroom\Models\ShowroomProduct;
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

            <div class="w-75 p-3 mx-auto" id="main-order-content" style="width: 750px;">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <form method="POST" action="{{ route('showroom-issue.confirm', $productIssue) }}"
                                    class="form-2">
                                    <div id="qr_ids_wrapper"></div>
                                    <input type="hidden" name="product_issue" value="{{ $productIssue->id }}" />
                                    <div class="wrapper-content">
                                        <div class="pd-all-20">
                                            <div class="card-header justify-content-between">
                                                <div class="flexbox-auto-right mr5">
                                                    <label
                                                        class="title-product-main text-no-bold">{{ __('Phiếu xuất kho') }}
                                                        <strong>{{ BaseHelper::clean(get_proposal_issue_product_code($productIssue->issue_code)) }}</strong>
                                                        -
                                                        Xuất từ: <strong>{{ $productIssue->warehouse_name }}
                                                            - {{ $productIssue->warehouseIssue->showroom->name }} <i
                                                                class="fa-solid fa-arrow-right"></i>
                                                            {{ $productIssue?->warehouse->name }}{{ optional($productIssue->warehouse->hub)->name ? ' - ' . $productIssue?->warehouse->hub->name : '' }}
                                                        </strong>
                                                    </label>
                                                    <div>
                                                        Mã đơn hàng:
                                                        <strong>{{ $productIssue->general_order_code }}</strong>
                                                    </div>
                                                    <div>
                                                        Người đề xuất:
                                                        <strong>{{ $productIssue->invoice_issuer_name }}</strong>
                                                    </div>
                                                    <div>
                                                        Tiêu đề: {{ $productIssue->title }}
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
                                                <table class="table-order table-divided table-vcenter card-table"
                                                    width="100%" id="table-content">
                                                    <tbody>
                                                        @php
                                                            $totalQty = 0;
                                                            $products = [];
                                                        @endphp
                                                        @foreach ($productIssue->productIssueDetail as $orderProduct)
                                                            @php
                                                                $products[] = $orderProduct;
                                                                $quantity = ShowroomProduct::where([
                                                                    'product_id' => $orderProduct->product_id,
                                                                    'warehouse_id' => $productIssue->warehouse_issue_id,
                                                                ])?->first()?->quantity_qrcode;
                                                            @endphp
                                                            @include(
                                                                'plugins/showroom::issue.batchs.product-item',
                                                                [
                                                                    'orderProduct' => $orderProduct,
                                                                    'totalQty' => &$totalQty,
                                                                    'quantity' => &$quantity,
                                                                    'warehouse_id' =>
                                                                        $productIssue->warehouse_issue_id,
                                                                ]
                                                            )
                                                        @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="card-footer pd-all-20 p-none-t">
                                            <div class="mt10">
                                                <div class="button-container">
                                                    <button class="btn btn-primary" type="submit" id="submit_btn"
                                                        disabled>Xác nhận
                                                        xuất</button>
                                                    <span class="tooltip-text">Quét QR trước khi xác nhận</span>
                                                </div>
                                                @php
                                                    echo Auth::user()->hasPermission(['showroom-issue.denied'])
                                                        ? '<button class="btn btn-danger" type="button" id="denyButton">Từ
                                                    chối</button>'
                                                        : '';
                                                @endphp
                                                        <button type="button" class="btn btn-primary d-inline" style="float: right"
                                                            data-bs-toggle="modal"data-type="product"
                                                            data-bs-target="#warehouseIssueModal" id="open_scan_modal">
                                                            <span class="me-2">
                                                                <i class="fa-solid fa-qrcode"></i>
                                                            </span>
                                                            Quét QR
                                                        </button>
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
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
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
                    <form id="denyForm" method="post" action="{{ route('showroom-issue.denied', $productIssue->id) }}">
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
    @include('plugins/qr-scan::scan-warehouse-issue-start', [
        'warehouse_id' => $productIssue->warehouse_issue_id,
        'warehouse_type' => ShowroomWarehouse::class,
        'data' => $products,
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
