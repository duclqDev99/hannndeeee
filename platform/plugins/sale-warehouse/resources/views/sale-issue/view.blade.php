@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\Warehouse\Enums\MaterialReceiptStatusEnum;
        use Botble\WarehouseFinishedProducts\Models\ProductBatch;
        use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
        use Botble\HubWarehouse\Models\Warehouse;
        use Botble\HubWarehouse\Models\QuantityProductInStock;
        use Botble\ProductQrcode\Enums\QRStatusEnum;
        use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
        use Botble\Media\Facades\RvMedia;

    @endphp
    <style>
        .flexbox-grid-default {
            position: relative;
        }

        .card-notify-year {
            position: absolute;
            right: -15px;
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

        .card-notify-year.blue {
            background: rgb(74, 74, 236);
        }

        .accordion-toggle {
            cursor: pointer;
        }

        .accordion-toggle:hover {
            background-color: #f5f5f5;
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

        .status-container {
            position: relative;
            margin-top: 20px;
        }

        .status-tag {
            position: absolute;
            font-size: 200%;
            left: 50%;
            transform: translateX(-50%) rotate(-45deg);
            text-align: right;
            color: #fff;
            padding: 5px 20px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
            user-select: none;
            opacity: 0.2;
        }
    </style>
    @php
        $strCenter = '12';
        if (isset($actualIssue)) {
            $strCenter = $productIssue?->proposal?->is_warehouse == 'tour' ? 4 : 6;
        }
        // if (isset($actualIssue)) {
        //     $strCenter = '4';
        // }
    @endphp
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
    <div class="widget__view row justify-content-center">
        <div class="card col-10">
            <div class="card-header">
                <div>

                    <h2 class="title">Thông tin phiếu xuất kho
                        {{ BaseHelper::clean(get_proposal_issue_product_code($productIssue->issue_code)) }}
                        <span class="status-container status-tag status-{{ $productIssue->status }}">
                            @php
                                echo $productIssue->status->toHtml();
                            @endphp

                        </span>
                    </h2>
                    <h3>
                        Mục đích xuất kho: <strong>{{ $productIssue->title }}</strong>
                    </h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho xuất:</label>
                            <strong>{{ $productIssue->warehouse_name }} -
                                {{ $productIssue->warehouseIssue->saleWarehouse->name }}</strong>
                        </div>
                        <div>
                            Người đề xuất:
                            <strong>{{ $productIssue->invoice_issuer_name }}</strong>
                        </div>
                        <div>
                            Ngày tạo:
                            {{ date('d-m-Y', strtotime($productIssue->created_at)) }}
                        </div>
                        <div class="info-group">
                            <label>Mã đơn hàng:</label>
                            <strong>{{ $productIssue->general_order_code ?: '—' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho nhận:</label>
                            <strong>{{ $productIssue->proposal->is_warehouse == 'tour' ? 'Xuất đi giải' : '' }}
                            </strong>
                        </div>
                        <div>
                            Người xuất kho:
                            <strong>{{ $productIssue->invoice_confirm_name ?: '—' }}</strong>
                        </div>
                        <div>
                            Ngày xuất:
                            {{ $productIssue->date_confirm ? date('d-m-Y', strtotime($productIssue->date_confirm)) : '—' }}
                        </div>
                        @if ($productIssue->reason_cancel)
                            <div> Lý do từ chối: <strong style="color: red">
                                    {{ $productIssue->reason_cancel }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body row">
                @isset($productIssue)
                    <div class="col-lg-{{ $strCenter }} col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">
                                            <div class="pd-all-20">

                                                <span class="card-notify-year red">Phiếu xuất kho</span>
                                            </div>
                                            @php
                                                $hasBatchProducts = false;
                                                $hasNonBatchProducts = false;
                                                $total = 0;
                                                $newIssue = false;
                                            @endphp
                                            <div class="card-body pd-all-20 p-none-t border-top-title-main">
                                                @csrf
                                                <div id="table-wrapper" class="table-wrap">

                                                    <div class="col-md-12">
                                                        <table class="table-order table-divided table-vcenter card-table"
                                                            width="100%" id="table-content">
                                                            <tbody>
                                                                {{ $productIssue->actual_qr_code }}
                                                                @foreach ($productIssue->productIssueDetail as $orderProduct)
                                                                    @php
                                                                        $products[] = $orderProduct;
                                                                        $total += $orderProduct->quantity;
                                                                    @endphp
                                                                    <tr class="item__product">
                                                                        <td class=" vertical-align-t" width="20%"
                                                                            style="margin:20px">

                                                                            <div class="wrap-img">
                                                                                <img class="thumb-image thumb-image-cartorderlist"
                                                                                    width="100px" height="100px"
                                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                    alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                            </div>
                                                                        </td>
                                                                        <td class="pl5 p-r5  " width = "40%">

                                                                            {{ $orderProduct->product->name }}
                                                                            <div>
                                                                                @foreach ($orderProduct->product->variationProductAttributes as $attribute)
                                                                                    @if ($attribute->color)
                                                                                        {{ 'Màu: ' . $attribute->title }}
                                                                                    @endif
                                                                                @endforeach
                                                                                @foreach ($orderProduct->product->variationProductAttributes as $attribute)
                                                                                    @if (!$attribute->color)
                                                                                        {{ 'Size: ' . $attribute->title }}
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                            <input type="text" class="widget__price"
                                                                                name="product[{{ $orderProduct->id }}][attr]"
                                                                                value="{{ $orderProduct->attribute }}"
                                                                                hidden="">
                                                                        </td>
                                                                        <td class="pl5 p-r5  " width = "30%">
                                                                            SKU: {{ $orderProduct->product->sku }}

                                                                        </td>
                                                                        <td class="pl5 p-r5  text-end" width="25%">
                                                                            <span style="white-space: nowrap;">Đề
                                                                                xuất:
                                                                                {{ $orderProduct->quantity }}
                                                                                sản phẩm
                                                                            </span>
                                                                        </td>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                                <tr>
                                                                    <td colspan="6" class="text-end h5">
                                                                        <h3>
                                                                            <strong>Tổng số lượng xuất: </strong> <span
                                                                                class="check__highlight">{{ $total }}
                                                                                sản phẩm</span>
                                                                        </h3>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>


                                                </div>


                                            </div>
                                            <div class="card-body pd-all-20 p-none-t">
                                                <div class="flexbox-grid-default">
                                                    <div class="flexbox-auto-right pl5">
                                                        <div class="row">

                                                            <div class="col-lg-6 col-md-12">
                                                                <div class="py-3 w-100">
                                                                    <label class="text-title-field">Ghi chú</label>
                                                                    <textarea class="form-control textarea-auto-height" name="description" rows="4" placeholder="Ghi chú"
                                                                        disabled="">{{ $productIssue->description }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 col-md-12">
                                                                <div class="py-3">
                                                                    <label class="text-title-field">Ngày dự kiến</label>
                                                                    @php
                                                                        $inputDate = $productIssue->expected_date;
                                                                        $formattedDate = date(
                                                                            'd-m-Y',
                                                                            strtotime($inputDate),
                                                                        );
                                                                    @endphp
                                                                    <input class="form-control flatpickr-input"
                                                                        data-date-format="d-m-Y" v-pre="1"
                                                                        placeholder="d-m-Y" data-input="" readonly="readonly"
                                                                        name="expected_date" type="text"
                                                                        value="{{ $formattedDate }}" id="expected_date"
                                                                        aria-invalid="false"
                                                                        aria-describedby="expected_date-error" disabled>

                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">
                                        {{ trans('plugins/warehouse::bill_export.title_form_out') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="myForm" action="{{ route('hub-issue.export-file') }}" method="POST">
                                        @csrf
                                        <input type="text" class="form-control" id="id"
                                            value="{{ $productIssue->id }}" name="id" hidden="hidden">
                                        <input type="text" class="form-control" id="type_bill"
                                            value="{{ trans('plugins/warehouse::bill_export.title_form_out') }}"
                                            name="type_bill" hidden="hidden">
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ trans('plugins/warehouse::bill_export.proposal_name') }}:</label>
                                            <input type="text" class="form-control" id="proposal_name"
                                                value="{{ $productIssue->invoice_issuer_name }}" name="proposal_name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ trans('plugins/warehouse::bill_export.receiver_name') }}:</label>
                                            <input type="text" class="form-control" id="receiver_name"
                                                name="receiver_name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ trans('plugins/warehouse::bill_export.storekeeper_name') }}:</label>
                                            <input type="text" class="form-control" id="storekeeper_name"
                                                name="storekeeper_name"
                                                value="{{ auth()->user()->last_name . ' ' . auth()->user()->first_name }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ trans('plugins/warehouse::bill_export.chief_accountant_name') }}:</label>
                                            <input type="text" class="form-control" id="chief_accountant_name"
                                                name="chief_accountant_name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ trans('plugins/warehouse::bill_export.manager_name') }}:</label>
                                            <input type="text" class="form-control" id="manager_name"
                                                name="manager_name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ trans('plugins/warehouse::bill_export.today') }}:</label>
                                            <input type="date" class="form-control" id="today"
                                                value="{{ date('Y-m-d') }}" name="today">
                                        </div>
                                        <div style="float: right">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-danger" data-bs-dismiss="modal"
                                                id="print" name="button_type" value="print"> <i
                                                    class="fas fa-print"></i>
                                                {{ __('In phiếu xuất kho') }}</button>
                                            <button type="submit" class="btn btn-primary" data-bs-dismiss="modal"
                                                id="download" name="button_type" value="download"><i
                                                    class="fas fa-download"></i>
                                                {{ __('Tải phiếu xuất kho') }}</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer"></div>
                            </div>
                        </div>
                    </div>
                @endisset
                @isset($actualIssue)
                    <div class="col-lg-{{ $strCenter }} col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content
                                        ">
                                            <span class="card-notify-year blue">Phiếu thực xuất</span>
                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <div class="card-body pd-all-20 p-none-t m-3" id="accordion ">
                                                    <h3>Danh sách lô</h3>
                                                    @php
                                                        $totalIssue = 0;
                                                    @endphp
                                                    @if (count($batchs) > 0)
                                                        @php
                                                            $groupedProducts = $batchs
                                                                ->filter(function ($detail) {
                                                                    return $detail->batch_id > 0;
                                                                })
                                                                ->groupBy('batch_id');
                                                        @endphp
                                                        <div id="batchTable" class="accordion">
                                                            @foreach ($groupedProducts as $batch_id => $products)
                                                                @php
                                                                    $isBatch = $products
                                                                        ->where('is_batch', 1)
                                                                        ->isNotEmpty();
                                                                @endphp
                                                                <div class="accordion-item">
                                                                    <div class="accordion-header d-flex align-items-center justify-content-between"
                                                                        id="heading{{ $batch_id }}">
                                                                        <button class="accordion-button collapsed"
                                                                            type="button" data-bs-toggle="collapse"
                                                                            data-bs-target="#collapse{{ $batch_id }}"
                                                                            aria-expanded="true"
                                                                            aria-controls="collapse{{ $batch_id }}">
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center w-100">
                                                                                <span>
                                                                                    {{ $products->first()->batch->batch_code }}
                                                                                    -
                                                                                    {{ $products->first()->batch->quantity }}
                                                                                    sản
                                                                                    phẩm
                                                                                </span>
                                                                                <span>
                                                                                    Ngày tạo:
                                                                                    {{ date('d-m-Y', strtotime($products->first()->batch->created_at)) }}
                                                                                </span>
                                                                            </div>
                                                                        </button>
                                                                        <div class="d-flex align-items-center">
                                                                            <button data-bs-toggle="tooltip"
                                                                                data-bs-original-title="Print QR Code"
                                                                                onclick="printQRCode(event)"
                                                                                class="btn btn-sm btn-icon btn-secondary print-qr-button"
                                                                                style="background-color: #8ACDD7"
                                                                                data-url="{{ route('receipt-product.print-qr-code', $batch_id) }}">
                                                                                <i class="fa fa-qrcode"></i><span
                                                                                    class="sr-only">Print QR
                                                                                    Code</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>

                                                                    <div id="collapse{{ $batch_id }}"
                                                                        class="accordion-collapse collapse"
                                                                        aria-labelledby="heading{{ $batch_id }}"
                                                                        data-bs-parent="#batchTable">
                                                                        <div class="accordion-body">
                                                                            <div class="product-list">
                                                                                @php
                                                                                    $displayedProducts = [];
                                                                                @endphp
                                                                                @foreach ($products as $product)
                                                                                    @if ($product->is_batch == 0 && !in_array($product->product_id, $displayedProducts))
                                                                                        @php
                                                                                            $displayedProducts[] =
                                                                                                $product->product_id;
                                                                                            $productCount = 0;
                                                                                        @endphp
                                                                                        @foreach ($products as $innerProduct)
                                                                                            @if ($innerProduct->product_id == $product->product_id)
                                                                                                @php
                                                                                                    $productCount++;
                                                                                                    $totalIssue++;

                                                                                                @endphp
                                                                                            @endif
                                                                                        @endforeach
                                                                                        <div class="product">
                                                                                            <div class="d-flex">
                                                                                                <div
                                                                                                    class="image align-items-center mb-3">
                                                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                                                        src="{{ RvMedia::getImageUrl($product?->product?->parentProduct?->first()?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                                        width="60px"
                                                                                                        alt="{{ $product->product->name }}">
                                                                                                </div>
                                                                                                <div class="details">
                                                                                                    <span
                                                                                                        class="name">{{ $product->product->name }}</span>
                                                                                                    <span class="attributes">
                                                                                                        @foreach ($product->product->variationProductAttributes as $attribute)
                                                                                                            @if ($attribute->color)
                                                                                                                {{ 'Màu: ' . $attribute->title }}
                                                                                                            @endif
                                                                                                        @endforeach

                                                                                                        @foreach ($product->product->variationProductAttributes as $attribute)
                                                                                                            @if (!$attribute->color)
                                                                                                                {{ 'Size: ' . $attribute->title }}
                                                                                                            @endif
                                                                                                        @endforeach
                                                                                                    </span>
                                                                                                    <span class="sku">SKU:
                                                                                                        {{ $product->product->sku }}</span>
                                                                                                    <span
                                                                                                        class="quantity">{{ $productCount }}
                                                                                                        sản phẩm</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div id="batchTable" class="accordion">
                                                            @foreach ($actualIssue->autualDetail as $batch)
                                                                @php
                                                                    $totalIssue += $batch?->quantity;

                                                                @endphp
                                                                <div class="accordion-item">
                                                                    <div class="accordion-header d-flex align-items-center justify-content-between"
                                                                        id="heading{{ $batch?->batch?->id }}">
                                                                        <button class="accordion-button collapsed"
                                                                            type="button" data-bs-toggle="collapse"
                                                                            data-bs-target="#collapse{{ $batch?->batch?->id }}"
                                                                            aria-expanded="true"
                                                                            aria-controls="collapse{{ $batch?->batch?->id }}">
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center w-100">
                                                                                <span>
                                                                                    {{ $batch?->batch?->batch_code }}
                                                                                    -
                                                                                    {{ $batch?->quantity }}
                                                                                    sản
                                                                                    phẩm
                                                                                </span>
                                                                                <span>
                                                                                    Ngày tạo:
                                                                                    {{ date('d-m-Y', strtotime($batch?->batch?->created_at)) }}
                                                                                </span>
                                                                            </div>
                                                                        </button>
                                                                        @if ($batch?->batch?->id)
                                                                            <div class="d-flex align-items-center">
                                                                                <button data-bs-toggle="tooltip"
                                                                                    data-bs-original-title="Print QR Code"
                                                                                    onclick="printQRCode(event)"
                                                                                    class="btn btn-sm btn-icon btn-secondary print-qr-button"
                                                                                    style="background-color: #8ACDD7"
                                                                                    data-url="{{ route('receipt-product.print-qr-code', $batch?->batch?->id) }}">
                                                                                    <i class="fa fa-qrcode"></i><span
                                                                                        class="sr-only">Print QR
                                                                                        Code</span>
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <div id="collapse{{ $batch?->batch?->id }}"
                                                                        class="accordion-collapse collapse"
                                                                        aria-labelledby="heading{{ $batch?->batch?->id }}"
                                                                        data-bs-parent="#batchTable">
                                                                        <div class="accordion-body">
                                                                            <div class="product-list">
                                                                                @php
                                                                                    $displayedProducts = [];
                                                                                @endphp
                                                                                <div class="product">
                                                                                    <div class="d-flex">
                                                                                        <div
                                                                                            class="image align-items-center mb-3">
                                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                                src="{{ RvMedia::getImageUrl($batch?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                                width="60px"
                                                                                                alt="{{ $batch->product->name }}">
                                                                                        </div>
                                                                                        <div class="details">
                                                                                            <span
                                                                                                class="name">{{ $batch->product->name }}</span>
                                                                                            <span class="attributes">
                                                                                                @foreach ($batch?->product?->variationProductAttributes as $attribute)
                                                                                                    @if ($attribute?->color)
                                                                                                        {{ 'Màu: ' . $attribute->title }}
                                                                                                    @endif
                                                                                                @endforeach

                                                                                                @foreach ($batch?->product?->variationProductAttributes as $attribute)
                                                                                                    @if (!$attribute->color)
                                                                                                        {{ 'Size: ' . $attribute->title }}
                                                                                                    @endif
                                                                                                @endforeach
                                                                                            </span>
                                                                                            <span class="sku">SKU:
                                                                                                {{ $batch?->product?->sku }}</span>
                                                                                            <span
                                                                                                class="quantity">{{ $batch?->quantity }}
                                                                                                sản phẩm</span>

                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>

                                                @php
                                                    $productDetails = $batchs->filter(function ($detail) {
                                                        return $detail->batch_id == 0 && $detail->is_batch == 0;
                                                    });

                                                @endphp
                                                <div class="card-body pd-all-20 p-none-t m-3">
                                                    <h3 class="">Danh sách lẻ</h3>
                                                    <div class="product-list">
                                                        @php
                                                            $existingProducts = [];
                                                        @endphp
                                                        @foreach ($productDetails as $detail)

                                                            @if (!in_array($detail->product_id, $existingProducts))
                                                                @php
                                                                    $existingProducts[] = $detail->product_id;
                                                                    $productCount = 0;
                                                                @endphp
                                                                @foreach ($productDetails as $innerProduct)
                                                                    @if ($innerProduct->product->id == $detail->product_id)
                                                                        @php
                                                                            $productCount++;
                                                                        @endphp
                                                                    @endif
                                                                @endforeach

                                                                @php
                                                                    $totalIssue+= $productCount;
                                                                @endphp
                                                                <div class="product">
                                                                    <div class="d-flex">
                                                                        <div class="image align-items-center mb-3">
                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                src="{{ RvMedia::getImageUrl($detail->product->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                width="60px"
                                                                                alt="{{ $detail->product->name }}">
                                                                        </div>
                                                                        <div class="details">
                                                                            <span
                                                                                class="name">{{ $detail->product->name }}</span>
                                                                            <span class="attributes">
                                                                                @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                    @if ($attribute->color)
                                                                                        {{ 'Màu: ' . $attribute->title }}
                                                                                    @endif
                                                                                @endforeach

                                                                                @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                    @if (!$attribute->color)
                                                                                        {{ 'Size: ' . $attribute->title }}
                                                                                    @endif
                                                                                @endforeach
                                                                            </span>
                                                                            <span class="sku">SKU:
                                                                                {{ $detail?->product?->sku }}</span>
                                                                            <span class="quantity">{{ $productCount }}
                                                                                sản phẩm</span>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="row">
                                                    <div class="col text-end">
                                                        <strong class="float-end">Tổng số lượng: {{ $totalIssue }} sản
                                                            phẩm </strong>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <div class="card meta-boxes mb-3">
                                                    <div class="card-header">
                                                        <h4 class="card-title">
                                                            Hình ảnh đính kèm
                                                        </h4>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="list-photos-gallery">
                                                            <div class="row g-2">

                                                                @if ($actualIssue->image)
                                                                    @php
                                                                        $images = json_decode($actualIssue->image);
                                                                    @endphp

                                                                    @foreach ($images as $image)
                                                                        <div
                                                                            class="col-lg-2 col-md-3 col-4 gallery-image-item-handler mb-2">
                                                                            <div class="custom-image-box mage-box">
                                                                                <img class="preview-image default-image"
                                                                                    src="{{ RvMedia::getImageUrl($image, 'thumb') }}"
                                                                                    alt="Preview image">
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <div
                                                                        class="col-lg-2 col-md-3 col-4 gallery-image-item-handler mb-2">
                                                                        <div class="custom-image-box mage-box">
                                                                            <img class="preview-image default-image"
                                                                                src="{{ RvMedia::getDefaultImage() }}"
                                                                                alt="Preview image">
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endisset
                @if ($productIssue?->proposal?->is_warehouse == 'tour')
                <div class="col-lg-{{ $strCenter }} col-md-12">
                    <div class="ui-layout">
                        <div class="flexbox-layout-sections">
                            <div class="flexbox-layout-section-primary mt20">
                                <div class="card ui-layout__item">
                                    <div class="wrapper-content
                                    ">
                                        <span class="card-notify-year" style="background-color: #FEC260;">Phiếu nhập lại giải</span>
                                        <div class="pd-all-20 p-none-t border-top-title-main"
                                            style="
                                                max-height: 500px;
                                                overflow: auto;
                                                scrollbar-width: none;
                                            ">
                                            <div class="card-body pd-all-20 p-none-t m-3">
                                                <h3 class="">Danh sách sản phẩm</h3>
                                                <div class="product-list">
                                                    @foreach ($productInput as $detail)
                                                        @php
                                                            // Tìm ra phần từ cùng product_id
                                                            $value = array_filter($products, function (
                                                                $item,
                                                            ) use ($detail) {
                                                                return $item->product_id == $detail->product->id;
                                                            });
                                                            // Lấy phần tử đầu tiên của mảng
                                                            if (!empty($value)) {
                                                                $value = current($value);
                                                                $value->quantity--;
                                                            }
                                                        @endphp
                                                        @if ($detail->batch_id == null || $detail->batch_id == 0)
                                                            @php
                                                                $totalIssue += $detail->quantity;
                                                            @endphp
                                                            <div class="product">
                                                                <div class="d-flex">
                                                                    <div class="image align-items-center mb-3">
                                                                        {{-- <img class="thumb-image thumb-image-cartorderlist" style="font-size: 10px;"
                                                                            src="{{ RvMedia::getImageUrl($detail->product?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            width="60px"
                                                                            alt="{{ $detail->product->name }}"> --}}
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            style="font-size: 10px;"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="top"
                                                                            title="{{ $detail->product->name }}"
                                                                            src="{{ RvMedia::getImageUrl($detail->product?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            width="60px"
                                                                            alt="{{ Str::limit($detail->product->name, 10) }}"
                                                                            title="{{ $detail->product->name }}">
                                                                    </div>
                                                                    <div class="details">
                                                                        <span
                                                                            class="name">{{ $detail->product->name }}</span>
                                                                        <br />
                                                                        <span class="attributes">
                                                                            @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                @if ($attribute->color)
                                                                                    {{ 'Màu: ' . $attribute->title }} -
                                                                                @endif
                                                                            @endforeach

                                                                            @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                @if (!$attribute->color)
                                                                                    {{ 'Size: ' . $attribute->title }}
                                                                                    <br />
                                                                                @endif
                                                                            @endforeach
                                                                        </span>
                                                                        <span class="sku">SKU:
                                                                            {{ $detail->product->sku }}</span> <br />
                                                                        {{-- <span class="quantity">{{ $detail->quantity }}
                                                                            sản phẩm</span> --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col text-end">
                                                <strong class="float-end">Tổng số lượng: {{ count($productInput) }}
                                                    sản
                                                    phẩm </strong>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-footer">
                                        <button type="button" class="btn btn-primary d-inline m-2"
                                            style="float:right" data-bs-toggle="modal"data-type="batch"
                                            data-bs-target="#warehouseIssueInSaleModal" id="open_scan_modal">
                                            <span class="me-2">
                                                <i class="fa-solid fa-qrcode"></i>
                                            </span>
                                            Quét QR sản phẩm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            </div>
            @if ($productIssue->status == \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::APPOROVED)
                <div class="card-footer text-center"
                    style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal"
                        data-bs-whatever="@mdo">
                        In phiếu xuất kho
                    </button>
                </div>
            @endif
        </div>
    </div>


    @include('plugins/qr-scan::scan-sale-warehouse-issue-in-sales', [
        'warehouse_id' => $productIssue->warehouse_issue_id,
        'warehouse_type' => SaleWarehouse::class,
        'warehouse_receipt_type' => $productIssue->warehouse_type,
        'data' => $batchs,
        'qrcode' => $productIssue->actualQrCode,
        'groupedOdd' => $groupedOdd,
        'products' => $products,
        'issue_id' => $productIssue->id,
    ])

    <script>
        window.warehouseType = @json($productIssue->warehouse_type);
        document.addEventListener('DOMContentLoaded', function() {
            const widget = document.querySelectorAll('.widget__view .ui-layout__item');
            $(document).ready(function() {
                $(".batch-header").click(function() {
                    var batchId = $(this).data("batch-id");
                    var detailsRow = $("#batch-details-" + batchId);

                    // Đóng tất cả các batch-details khác
                    $(".batch-details").not(detailsRow).hide();

                    // Mở hoặc đóng chi tiết cho batch hiện tại
                    detailsRow.toggle();
                });
            });
            if (widget && widget.length === 2) {
                const wp_porposal = widget[0];
                const wp_receipt = widget[1];

                const itemHighlightPorposal = wp_porposal.querySelectorAll('.check__highlight');
                const itemHighlightReceipt = wp_receipt.querySelectorAll('.check__highlight');

                for (let index = 0; index < itemHighlightPorposal.length; index++) {
                    if (itemHighlightPorposal[index].textContent !== itemHighlightReceipt[index].textContent) {
                        itemHighlightPorposal[index].style.color = 'red';
                        itemHighlightPorposal[index].style.fontSize = '1.2em';
                        itemHighlightPorposal[index].style.fontWeight = 'bold';
                        itemHighlightPorposal[index].style.textDecoration = 'underline';

                        itemHighlightReceipt[index].style.color = 'red';
                        itemHighlightReceipt[index].style.fontSize = '1.2em';
                        itemHighlightReceipt[index].style.fontWeight = 'bold';
                        itemHighlightReceipt[index].style.textDecoration = 'underline';
                    }
                }
            }

        })
    </script>
@stop
