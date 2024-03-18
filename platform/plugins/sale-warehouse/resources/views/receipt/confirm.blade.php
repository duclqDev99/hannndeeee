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
    use Botble\Base\Facades\BaseHelper;
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
                                <form method="POST" action="{{ route('sale-receipt.confirm', $productIssue->id) }}"
                                    class="form-2">
                                    <div id="qr_ids_wrapper"></div>
                                    <input type="hidden" name="product_issue" value="{{ $productIssue->id }}" />
                                    <div class="wrapper-content">
                                        <div class="pd-all-20">
                                            <div class="card">
                                                <div class="card-header">
                                                    <div>
                                                        <h2 class="title">Thông tin phiếu nhập kho
                                                            {{ BaseHelper::clean(get_proposal_receipt_product_code($productIssue->receipt_code)) }}
                                                        </h2>
                                                        <div>
                                                            <h3>
                                                                Mục đích: <strong>{{ $productIssue->title }}</strong>
                                                            </h3>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info-group">
                                                                <label>Kho xuất:</label>
                                                                <strong>{{ $productIssue->warehouse->name }}
                                                                    -
                                                                    {{ $productIssue->warehouse->hub->name }}</strong>
                                                            </div>
                                                            <div>
                                                                Người đề xuất:
                                                                <strong>{{ $productIssue->invoice_issuer_name }}</strong>
                                                            </div>
                                                            <div class="info-group">
                                                                <label>Mã đơn hàng:</label>
                                                                <strong>{{ $productIssue->general_order_code ?: '—' }}</strong>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info-group">
                                                                <label>Kho nhận:</label>
                                                                <strong>{{ $productIssue->warehouseReceipt->name }} -
                                                                    {{ $productIssue->warehouseReceipt?->saleWarehouse?->name }}
                                                                </strong>

                                                            </div>
                                                            <div>
                                                                Ngày tạo:
                                                                {{ date('d-m-Y', strtotime($productIssue->created_at)) }}
                                                            </div>


                                                        </div>
                                                    </div>
                                                    <br><br>
                                                    @if ($productIssue->hubIssue->proposal->policy)
                                                        <div>
                                                            @php
                                                                $policy = $productIssue->hubIssue->proposal->policy;
                                                                $value = 0;
                                                                if ($policy->type_date_active == 'date') {
                                                                    $value = 1;
                                                                } elseif ($policy->type_date_active == 'month') {
                                                                    $value = 30;
                                                                } else {
                                                                    $value = 365;
                                                                }
                                                                $dateAcceipt = (int) $policy->time_active * $value;
                                                                $date_type = $policy->type_time;
                                                            @endphp
                                                            <div>
                                                                <div class="text h2" style="color: #ff4444"> Chính sách giảm
                                                                    giá
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div>
                                                                    <div class="row">
                                                                        <div class="col-lg-6 col-12 col-md-6">
                                                                            <p><strong>Tên chính sách:</strong>
                                                                                {{ $policy->name }}
                                                                            </p>
                                                                            <p><strong>Ngày bắt đầu:</strong>
                                                                                {{ $policy->start_date }}</p>
                                                                            <p><strong>Số lượng:</strong>
                                                                                {{ $policy->quantity > 0 ? $policy->quantity : 'Không giới hạn' }}
                                                                            </p>
                                                                            <p><strong>Giảm: </strong>{{ $policy->value }}
                                                                                {{ $policy->type_option == 'amount' ? 'VNĐ' : '%' }}
                                                                            </p>
                                                                        </div>
                                                                        <div class="col-lg-6 col-12">
                                                                            <p><strong>Mã chính sách:</strong>
                                                                                {{ $policy->code }}
                                                                            </p>
                                                                            <p><strong>Ngày kết thúc:</strong>
                                                                                {{ $policy->end_date ?? 'Không giới hạn' }}
                                                                            </p>

                                                                                Sản phẩm có
                                                                                <strong>{{ $policy->type_time == 'date_production' ? ' ngày sản xuất' : ' ngày nhập kho' }}
                                                                                </strong> sau
                                                                                <strong>
                                                                                    {{ $policy->time_active }}
                                                                                    {{ $policy->type_date_active == 'date' ? ' ngày' : ($policy->type_date_active == 'month' ? 'tháng' : 'năm') }}</strong>
                                                                            </p>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
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
                                            @php
                                                $total = 0;
                                                $groupedProducts = [];
                                                $batches = [];
                                                $groupedByOdd = $productIssue->receiptDetail->filter(function (
                                                    $detail,
                                                ) {
                                                    return $detail->batch_id == 0;
                                                });
                                            @endphp
                                            <div id="accordion">
                                                <h3 class="m-3">Danh sách lô</h3>
                                                <div id="accordion">
                                                    @foreach ($productIssue->receiptDetail as $index => $productData)
                                                        @if ($productData->batch_id > 0)
                                                            @php
                                                                $batches[] = $productData->batch;
                                                                $total += $productData?->batch?->quantity;
                                                            @endphp
                                                            <div class="card m-2">
                                                                <div class="accordion" id="accordionExample">
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header"
                                                                            id="heading{{ $index }}">
                                                                            <button class="accordion-button collapsed"
                                                                                type="button" data-bs-toggle="collapse"
                                                                                data-bs-target="#collapse{{ $index }}"
                                                                                aria-expanded="false"
                                                                                aria-controls="collapse{{ $index }}">
                                                                                Lô: {{ $productData->batch->batch_code }} -
                                                                                Số lượng:
                                                                                {{ $productData->batch->quantity }} sản
                                                                                phẩm
                                                                            </button>
                                                                        </h2>
                                                                        <div id="collapse{{ $index }}"
                                                                            class="accordion-collapse collapse"
                                                                            aria-labelledby="heading{{ $index }}"
                                                                            data-bs-parent="#accordionExample">
                                                                            <div class="accordion-body">
                                                                                <div class="product-list">
                                                                                    @php
                                                                                        $displayedProducts = [];
                                                                                    @endphp
                                                                                    @foreach ($productData->batch->productInBatch as $detail)
                                                                                        @if (!in_array($detail->product_id, $displayedProducts))
                                                                                            @php
                                                                                                $displayedProducts[] =
                                                                                                    $detail->product_id;
                                                                                                $productCount = 0;
                                                                                            @endphp
                                                                                            @foreach ($productData->batch->productInBatch as $innerProduct)
                                                                                                @if ($innerProduct->product_id == $detail->product_id)
                                                                                                    @php
                                                                                                        $productCount++;
                                                                                                    @endphp
                                                                                                @endif
                                                                                            @endforeach
                                                                                            <div class="product">
                                                                                                <div class="d-flex">
                                                                                                    <div
                                                                                                        class="image align-items-center mb-3">
                                                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                                                            src="{{ RvMedia::getImageUrl($detail->product?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                                            width="60px"
                                                                                                            alt="{{ $detail->product->name }}">
                                                                                                    </div>
                                                                                                    <div class="details">
                                                                                                        <span
                                                                                                            class="name">{{ $detail->product->name }}</span>
                                                                                                        <span
                                                                                                            class="attributes">
                                                                                                            @foreach ($detail?->product?->variationProductAttributes as $attribute)
                                                                                                                @if ($attribute?->color)
                                                                                                                    {{ 'Màu: ' . $attribute->title }}
                                                                                                                @endif
                                                                                                            @endforeach

                                                                                                            @foreach ($detail?->product?->variationProductAttributes as $attribute)
                                                                                                                @if (!$attribute->color)
                                                                                                                    {{ 'Size: ' . $attribute->title }}
                                                                                                                @endif
                                                                                                            @endforeach
                                                                                                            <span
                                                                                                                class="sku">SKU:
                                                                                                                {{ $detail->product->sku }}</span>
                                                                                                            <span
                                                                                                                class="quantity">{{ $productCount }}
                                                                                                                sản
                                                                                                                phẩm</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endif
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>


                                            <div id="table-wrapper" class="table-wrap">
                                                <h3 class="m-3" style="color: #ff4444">Danh sách lẻ</h3>
                                                <div class="product-list">
                                                    @php
                                                        $existingProducts = [];
                                                    @endphp

                                                    @foreach ($groupedByOdd as $odd)
                                                        @php
                                                            $batches[] = $odd->qrcode_id;
                                                        @endphp
                                                        @if (!in_array($odd->product->id, $existingProducts))
                                                            @php
                                                                $existingProducts[] = $odd->product->id;
                                                                $productCount = 0;
                                                            @endphp
                                                            @foreach ($groupedByOdd as $innerProduct)
                                                                @if ($innerProduct->product->id == $odd->product_id)
                                                                    @php
                                                                        $productCount++;
                                                                        $total++;
                                                                    @endphp
                                                                @endif
                                                            @endforeach
                                                            <div class="product">
                                                                <div class="d-flex">
                                                                    <div class="image mr-3">
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            src="{{ RvMedia::getImageUrl($odd->product?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            alt="{{ $odd->product->name }}">

                                                                    </div>
                                                                    <div class="details">
                                                                        <span
                                                                            class="name">{{ $odd->product->name }}</span>
                                                                        <div class="attributes">Màu
                                                                            {{ $odd->product->variationProductAttributes[0]->title ?? '---' }}
                                                                            - Size
                                                                            {{ $odd->product->variationProductAttributes[1]->title ?? '---' }}
                                                                        </div>
                                                                        <div class="sku">SKU:
                                                                            {{ $odd->product->sku }}</div>
                                                                        <div class="quantity">{{ $productCount }}
                                                                            sản phẩm</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                <div class="row">
                                                    <div class="col text-end">
                                                        <strong class="float-end">Tổng số lượng: {{ $total }} sản
                                                            phẩm </strong>
                                                    </div>
                                                </div>


                                            </div>

                                        </div>
                                        <div class="card-footer pd-all-20 p-none-t">
                                            <div class="mt10">
                                                {{-- <button type="button" class="btn btn-primary d-inline"
                                                    data-bs-toggle="modal"data-type="batch"
                                                    data-bs-target="#scanBatchInModal" id="open_scan_modal">
                                                    <span class="me-2">
                                                        <i class="fa-solid fa-qrcode"></i>
                                                    </span>
                                                    Quét QR
                                                </button>
                                                <button class="btn btn-primary" type="submit" id="submit_btn" disabled>Xác
                                                    nhận
                                                    nhập</button>
                                               <span class="tooltip-text">Quét QR trước khi xác nhập</span> --}}
                                                <div class="button-container">
                                                    <button class="btn btn-primary" id="confirmButton" type="button">
                                                        Xác nhận nhập
                                                    </button>
                                                </div>
                                                @php
                                                    echo Auth::user()->hasPermission(['showroom-receipt.denied'])
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

                    <h3>Từ chối xuất kho {{ $productIssue->proposal_code }} </h3>

                </div>
                <div class="modal-body">
                    <form id="denyForm" method="post" action="{{ route('sale-receipt.denied', $productIssue->id) }}">
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
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-status bg-success"></div>
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="modal-body text-center py-4">
                        <div class="mb-2">
                            <span class="icon-tabler-wrapper icon-lg text-success">

                                @php
                                    echo BaseHelper::renderIcon('ti ti-check');
                                @endphp
                            </span>
                        </div>

                        <h3>Xác nhận nhập kho</h3>

                    </div>
                </div>
                <div class="modal-body">
                    <!-- Add content for the modal body here -->
                    <!-- Example: Are you sure you want to confirm the entry? -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>
    @php
        $productIssue['product_in_batch'] = $productIssue->receipt_detail;
        $productIssue['wh_departure_name'] = $productIssue->warehouse_name;
    @endphp
    {{-- modal --}}
    @include('plugins/qr-scan::scan-batch-in', [
        'batches' => $batches,
        'wh_departure_id' => $productIssue->warehouse_id,
        'warehouse_id' => $productIssue->warehouse_receipt_id,
        'warehouse' => $productIssue,
    ])
    <script>
        $("#denyButton").on("click", function() {
            $("#denyModal").modal("show");
        });
        $("#confirmButton").on("click", function() {
            $("#confirmModal").modal("show");
        });
        $("#submit_btn").on("click", function() {
            $(this).prop('disabled', true);
            $(".form-2").submit();
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
    .accordion-header {
        margin-bottom: 10px;
    }



    .accordion-button:hover {
        background-color: #e9ecef;
    }



    .accordion-button:focus {
        outline: none;
    }

    .accordion-header {
        margin-bottom: 10px;
        width: 100%;
        /* Đặt chiều rộng của accordion-header là 100% */
    }

    .collapse {
        margin: 10px;
    }

    /* Thiết lập các thuộc tính mặc định cho các phần tử */
    .product-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .product {
        flex: 1 0 calc(50% - 20px);
        /* Chia layout thành 3 cột */
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .product .d-flex {
        display: flex;
    }

    .product .image img {
        max-width: 100px;
    }

    .product .details {
        flex-grow: 1;
    }

    .product .name {
        font-weight: bold;
    }

    .product .attributes,
    .product .sku,
    .product .quantity {
        font-size: 14px;
    }

    .product .attributes {
        color: #666;
    }

    .product .sku {
        color: #999;
    }

    .product .quantity {
        color: #333;
        font-weight: bold;
    }

    /* Đáp ứng: Hiển thị một cột trên màn hình nhỏ */
    @media screen and (max-width: 768px) {
        .product {
            flex: 1 0 calc(50% - 20px);
            /* Chia layout thành 2 cột */
        }
    }
</style>

@push('header')
    <meta name="apple-mobile-web-app-capable" content="yes">
@endpush





{{-- @push('footer')
    <script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
@endpush --}}
