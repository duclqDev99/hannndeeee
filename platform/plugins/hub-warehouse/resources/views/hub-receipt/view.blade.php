@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\HubWarehouse\Models\ActualReceiptQrcode;
    use Botble\HubWarehouse\Models\Warehouse;

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

        .card-notify-year.blue {
            background: rgb(74, 74, 236);
        }

        .tag__product {
            background: rgb(233, 99, 99);
            color: #fff;
            padding: 5px 10px;
            border-radius: 99px;
            font-size: .85em;
            text-align: center;
            box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
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
        $strCenter = 12;
        if (isset($actual)) {
            $strCenter = 6;
        }
    @endphp
    <div class="widget__view row row-cards justify-content-center
    ">
        <div class="card col-10">
            <div class="card-header">
                <div>

                    <h2 class="title">Thông tin phiếu nhập kho
                        {{ BaseHelper::clean(get_proposal_receipt_product_code($receipt->receipt_code)) }}
                        <span class="status-container status-tag status-{{ $receipt->status }}">
                            @php
                                echo $receipt->status->toHtml();
                            @endphp

                        </span>
                    </h2>
                    <h3>
                        <div>
                            Mục đích nhập kho: <strong>{{ $receipt->title }}</strong>
                        </div>
                    </h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho xuất:</label>
                            <strong>
                                @php
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
                        <div>
                            Người đề xuất:
                            <strong>{{ $receipt->invoice_issuer_name }}</strong>
                        </div>
                        <div>
                            Ngày tạo:
                            {{ date('d-m-Y', strtotime($receipt->created_at)) }}
                        </div>
                        <div class="info-group">
                            <label>Mã đơn hàng:</label>
                            <strong>{{ $receipt->general_order_code ?: '—' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho nhận:</label>
                            <strong>{{ $receipt->warehouseReceipt->name }} -
                                {{ $receipt->warehouseReceipt?->hub?->name }}
                            </strong>

                        </div>
                        <div>
                            Người nhập kho:
                            <strong>{{ $receipt->invoice_confirm_name ?: '—' }}</strong>
                        </div>
                        <div>
                            Ngày nhập:
                            {{ $receipt->date_confirm ? date('d-m-Y', strtotime($receipt->date_confirm)) : '—' }}
                        </div>


                    </div>
                </div>

            </div>
            <div class="card-body row">
                @isset($receipt)
                    <div class="col-lg-{{ $strCenter }} col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">
                                            <span class="card-notify-year blue">Phiếu nhập kho</span>


                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <div class="table-wrap">
                                                    <table class="table-order table-divided table-vcenter card-table w-100">
                                                        <tbody>
                                                            @php
                                                                $totalQty = 0;
                                                            @endphp
                                                            @foreach ($receipt->receiptDetail as $orderProduct)
                                                                <tr class="item__product">
                                                                    <td class="width-60-px min-width-60-px vertical-align-t">
                                                                        <div class="wrap-img">
                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                style="max-width: 100px;"
                                                                                src="{{ RvMedia::getImageUrl($orderProduct?->product?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                alt="{{ $orderProduct?->product?->name }}">
                                                                        </div>
                                                                    </td>
                                                                    <td class="p-3 min-width-200-px">
                                                                        {{ $orderProduct?->product_name }}
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
                                                                    </td>
                                                                    <td class="p-3">
                                                                        <div class="inline_block">
                                                                            <span>SKU:
                                                                                <strong>{{ $orderProduct?->sku }}</strong></span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="p-3">
                                                                        Số lượng: <span
                                                                            class="check__highlight">{{ $orderProduct?->quantity }}
                                                                            sản
                                                                            phẩm
                                                                            {{-- {{ $receipt->from_product_issue == 1 ? 'lô' : '' }} --}}
                                                                        </span>
                                                                    </td>
                                                                </tr>

                                                                @php
                                                                    $totalQty += $orderProduct->quantity;
                                                                @endphp
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="5" class="text-end h5">
                                                                    <strong>Tổng số lượng: </strong> <span
                                                                        class="widget__amount">{{ $totalQty }} sản
                                                                        phẩm</span>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="card-body pd-all-20 p-none-t">
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3 w-100">
                                                            <label
                                                                class="text-title-field">{{ trans('plugins/ecommerce::order.note') }}</label>
                                                            <textarea class="form-control textarea-auto-height" name="description" rows="4" placeholder="{{ __('Ghi chú') }}"
                                                                disabled>{{ $receipt->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $inputDate = $receipt->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                                        $formattedDate = date('d/m/Y', strtotime($inputDate));
                                                    @endphp
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3">
                                                            <label class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                            <input class="form-control flatpickr-input"
                                                                value="{{ $formattedDate }}" data-date-format="d-m-Y"
                                                                v-pre="1" placeholder="d-m-Y" data-input=""
                                                                readonly="readonly" name="expected_date" type="text"
                                                                id="expected_date" aria-invalid="false"
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
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">
                                        {{ trans('plugins/warehouse::bill_export.title_form_in') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="myForm" action="{{ route('hub-receipt.export-file') }}" method="POST">
                                        @csrf
                                        <input type="text" class="form-control" id="id" value="{{ $receipt->id }}"
                                            name="id" hidden="hidden">
                                        <input type="text" class="form-control" id="type_bill"
                                            value="{{ trans('plugins/warehouse::bill_export.title_form_in') }}"
                                            name="type_bill" hidden="hidden">
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ __('Người lập biểu') }}:</label>
                                            <input type="text" class="form-control" id="proposal_name"
                                                value="{{ $receipt->invoice_issuer_name }}" name="proposal_name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ __('Kế toán hoặc trưởng bộ phận') }}:</label>
                                            <input type="text" class="form-control" id="receiver_name"
                                                name="receiver_name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ trans('Người thủ kho') }}:</label>
                                            <input type="text" class="form-control" id="storekeeper_name"
                                                name="storekeeper_name"
                                                value="{{ auth()->user()->last_name . ' ' . auth()->user()->first_name }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name"
                                                class="col-form-label required">{{ __('Người giao hàng') }}:</label>
                                            <input type="text" class="form-control" id="chief_accountant_name"
                                                name="chief_accountant_name">
                                        </div>
                                        {{-- <div class="mb-3">
                                <label for="recipient-name" class="col-form-label required">{{ trans('plugins/warehouse::bill_export.manager_name') }}:</label>
                                <input type="text" class="form-control" id="manager_name" name="manager_name">
                            </div> --}}
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
                                                {{ __('In phiếu nhập kho') }}</button>
                                            <button type="submit" class="btn btn-primary" data-bs-dismiss="modal"
                                                id="download" name="button_type" value="download"><i
                                                    class="fas fa-download"></i>
                                                {{ __('Tải phiếu nhập kho') }}</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">

                                </div>
                            </div>
                        </div>
                    </div>
                @endisset
                @isset($actual)
                    <div class="col-lg-6 col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">
                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <span class="card-notify-year red">Phiếu thực nhập</span>

                                                <div class="card-body pd-all-20 p-none-t m-3">
                                                    <h3>Danh sách lô</h3>
                                                    {{-- <button type="button" class="btn btn-primary print-qr-button"
                                                data-bs-toggle="tooltip" data-bs-original-title="Print QR Code"
                                                onclick="printQRCode()"
                                                data-url={{ route('hub-receipt.print-qr-code-all', $receipt->id) }}>In tất cả
                                                QR code</button> --}}

                                                    <div id="batchTable" class="accordion">
                                                        @foreach ($batchs as $key => $batch)
                                                            <div class="accordion-item">
                                                                <div class="accordion-header d-flex align-items-center justify-content-between"
                                                                    id="heading{{ $batch?->id }}">
                                                                    <button class="accordion-button collapsed" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#collapse{{ $batch?->id }}"
                                                                        aria-expanded="true"
                                                                        aria-controls="collapse{{ $batch?->id }}">
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-center w-100">
                                                                            <span>
                                                                                {{ $batch?->batch->batch_code }} -
                                                                                {{ $batch->quantity }} sản phẩm
                                                                            </span>
                                                                            <span>
                                                                                Ngày tạo:
                                                                                {{ date('d-m-Y', strtotime($batch->created_at)) }}
                                                                            </span>
                                                                        </div>
                                                                    </button>
                                                                    <div class="d-flex align-items-center">
                                                                        <button data-bs-toggle="tooltip"
                                                                            data-bs-original-title="Print QR Code"
                                                                            onclick="printQRCode(event)"
                                                                            class="btn btn-sm btn-icon btn-secondary print-qr-button"
                                                                            style="background-color: #8ACDD7"
                                                                            data-url="{{ route('hub-receipt.print-qr-code', $batch?->batch->id) }}">
                                                                            <i class="fa fa-qrcode"></i><span
                                                                                class="sr-only">Print QR
                                                                                Code</span>
                                                                        </button>
                                                                    </div>
                                                                </div>

                                                                <div id="collapse{{ $batch->id }}"
                                                                    class="accordion-collapse collapse"
                                                                    aria-labelledby="heading{{ $batch->id }}"
                                                                    data-bs-parent="#batchTable">
                                                                    <div class="accordion-body">
                                                                        <div class="product-list">
                                                                            @php
                                                                                $batchGroup = $batch->productInBatch->groupBy('product_id');
                                                                            @endphp
                                                                            @foreach ($batchGroup as $batchDetail)
                                                                                <div class="product">
                                                                                    <div class="d-flex">
                                                                                        <div
                                                                                            class="image align-items-center mb-3">
                                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                                src="{{ RvMedia::getImageUrl($batchDetail?->first()?->product?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                                width="60px"
                                                                                                alt="{{ $batchDetail->first()->product->name }}">
                                                                                        </div>
                                                                                        <div class="details">
                                                                                            <span
                                                                                                class="name">{{ $batchDetail->first()->product->name }}</span>
                                                                                            <span class="attributes">
                                                                                                @foreach ($batchDetail->first()->product->variationProductAttributes as $attribute)
                                                                                                    @if ($attribute->color)
                                                                                                        {{ 'Màu: ' . $attribute->title }}
                                                                                                    @endif
                                                                                                @endforeach

                                                                                                @foreach ($batchDetail->first()->product->variationProductAttributes as $attribute)
                                                                                                    @if (!$attribute->color)
                                                                                                        {{ 'Size: ' . $attribute->title }}
                                                                                                    @endif
                                                                                                @endforeach
                                                                                            </span>
                                                                                            <span class="sku">SKU:
                                                                                                {{ $batchDetail->first()->product->sku }}</span>
                                                                                            <span
                                                                                                class="quantity">{{ $batchDetail->count() }}
                                                                                                sản phẩm</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="card-body pd-all-20 p-none-t m-3">
                                                    <h3 class="m-3">Danh sách lẻ</h3>
                                                    <div class="product-list">
                                                        @if (count($receipt->qrCode) > 0)
                                                            @php
                                                                $existingProducts = [];
                                                                $groupedOdd = $receipt->qrCode->filter(function ($detail) {
                                                                    return $detail->batch_id == null;
                                                                });
                                                            @endphp
                                                            @foreach ($groupedOdd as $odd)
                                                                @if (!in_array($odd->product->id, $existingProducts))
                                                                    @php
                                                                        $existingProducts[] = $odd->product->id;
                                                                        $productCount = 0;
                                                                    @endphp
                                                                    @foreach ($groupedOdd as $innerProduct)
                                                                        @if ($innerProduct->product->id == $odd->product_id)
                                                                            @php
                                                                                $productCount++;
                                                                            @endphp
                                                                        @endif
                                                                    @endforeach
                                                                    <div class="product">
                                                                        <div class="d-flex">
                                                                            <div class="image align-items-center mb-3">
                                                                                <img class="thumb-image thumb-image-cartorderlist"
                                                                                    src="{{ RvMedia::getImageUrl($odd->product->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                    width="60px"
                                                                                    alt="{{ $odd->product->name }}">
                                                                            </div>
                                                                            <div class="details">
                                                                                <span
                                                                                    class="name">{{ $odd->product->name }}</span>
                                                                                <span class="attributes">
                                                                                    @foreach ($odd->product->variationProductAttributes as $attribute)
                                                                                        @if ($attribute->color)
                                                                                            {{ 'Màu: ' . $attribute->title }}
                                                                                        @endif
                                                                                    @endforeach

                                                                                    @foreach ($odd->product->variationProductAttributes as $attribute)
                                                                                        @if (!$attribute->color)
                                                                                            {{ 'Size: ' . $attribute->title }}
                                                                                        @endif
                                                                                    @endforeach
                                                                                </span>
                                                                                <span class="sku">SKU:
                                                                                    {{ $odd?->product?->sku }}</span>
                                                                                <span class="quantity">{{ $productCount }}
                                                                                    sản phẩm</span>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            @foreach ($actual->actualDetail as $detail)

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
                                                                            <span class="quantity">{{ $detail->quantity }}
                                                                                sản phẩm</span>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <div class="card meta-boxes mb-3" id="gallery_wrap">
                                                    <div class="card-header">
                                                        <h4 class="card-title">
                                                            Hình ảnh đính kèm
                                                        </h4>
                                                    </div>
                                                    @if (!empty($actual?->image))
                                                        <div class="card-body">
                                                            <input id="gallery-data" class="form-control" name="gallery"
                                                                type="hidden" value="[]">
                                                            <div>
                                                                <div class="list-photos-gallery">
                                                                    <div class="row g-2" id="list-photos-items">
                                                                        @foreach (json_decode($actual?->image) as $key => $image)
                                                                            <div class="col-md-2 col-sm-3 col-4 photo-gallery-item"
                                                                                data-id="{{ $key }}"
                                                                                data-img="{{ RvMedia::getImageUrl($image?->img, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                data-description="{{ $image?->description }}">
                                                                                <div class="gallery_image_wrapper">
                                                                                    <img src="{{ RvMedia::getImageUrl($image?->img, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                        alt="image" loading="lazy">
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="modal fade modal-blur" id="edit-gallery-item"
                                                                tabindex="-1" data-select2-dropdown-parent="true"
                                                                style="display: none;" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable "
                                                                    role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Mô tả của hình ảnh</h5>
                                                                            <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        </div>

                                                                        <div class="modal-body">
                                                                            <input type="text" class="form-control"
                                                                                id="gallery-item-description"
                                                                                placeholder="Mô tả...">
                                                                        </div>

                                                                        <div class="modal-footer">
                                                                            <div class="btn-list">
                                                                                <button class="btn" type="button"
                                                                                    data-bs-dismiss="modal">Thoát</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
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

                @endisset
            </div>
            @if ($receipt->status == \Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum::APPOROVED && isset($actual))
                <div class="card-footer text-center"
                    style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal"
                        data-bs-whatever="@mdo">
                        {{ __('In phiếu nhập kho') }}
                    </button>
                </div>
            @endif
        </div>

        @if (!empty($batchs))
            @foreach ($batchs as $key => $batch)
                <div class="modal fade" id="view-detail-batch-{{ $batch?->id }}" tabindex="-1"
                    aria-labelledby="view-detail-batch" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="view-detail-batch">Chi tiết lô hàng</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @foreach ($batch?->productInBatch as $product)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="wrap-img">
                                            @php
                                                $logoPath = 'images/logo-handee.png';
                                                $qrCodeWithLogo = QrCode::size(100)
                                                    ->format('png')
                                                    ->merge($logoPath, 0.3, true)
                                                    ->errorCorrection('H')
                                                    ->generate($product->productQrcode);
                                            @endphp
                                            <img class="thumb-image thumb-image-cartorderlist"
                                                style="width: 100px; object-fit: contain;"
                                                src="data:image/png;base64, {!! base64_encode($qrCodeWithLogo) !!} "
                                                alt="{{ $product->product_name }}">
                                            {{-- <img
                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                    src="data:image/png;base64, {{ $product->statusQrCode->qr_code }}"
                                    alt="{{ $product->product_name }}"
                                > --}}
                                        </div>
                                        <h4 class="my-0">{{ $product->product_name }},
                                            {{ $product->product->variationProductAttributes->first()->title ?? '' }},
                                            {{ $product->product->variationProductAttributes->last()->title ?? '' }}</h4>
                                        <div>Sku: {{ $product->sku }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="modal-footer"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @stop
