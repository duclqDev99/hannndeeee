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


        .status-container {
            position: relative;
            margin-top: 20px;
        }

        .status-tag {
            position: absolute;
            bottom: -30px;
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
    <div class="widget__view row justify-content-center">
        @isset($receipt)
            <div class="col-lg-6 col-md-12">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="card">
                                            <div class="card-header">
                                                <div>
                                                    <h2 class="title">Thông tin phiếu nhập kho
                                                        {{ BaseHelper::clean(get_proposal_receipt_product_code($receipt->receipt_code)) }}
                                                        <span class="status-container status-tag status-{{ $receipt->status }}">
                                                            @php
                                                                echo $proposal->status->toHtml();
                                                            @endphp

                                                        </span>
                                                    </h2>
                                                    <h3> Mục đích nhập kho:
                                                        {{ $receipt->title }}
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
                                                            Ngày tạo:
                                                            {{ date('d-m-Y', strtotime($receipt->created_at)) }}
                                                        </div>
                                                        @if ($receipt->reason_cancel)
                                                            <div> Lý do từ chối: <strong style="color: red">
                                                                    {{ $receipt->reason_cancel }}</strong></div>
                                                        @endif
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        @if ($receipt->status == \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::PENDING)
                                            <span class="card-notify-year blue">Phiếu chờ nhập kho</span>
                                        @elseif($receipt->status == \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::DENIED)
                                            <span class="card-notify-year red">Từ chối nhập kho</span>
                                        @else
                                            <span class="card-notify-year red">Phiếu đã nhập kho</span>
                                        @endif
                                    </div>

                                    <div class="card-body pd-all-20 p-none-t border-top-title-main">
                                        @csrf
                                        <div id="table-wrapper" class="table-wrap">
                                            <div class="col-md-12">
                                                <table class="table-order table-divided table-vcenter card-table" width="100%"
                                                    id="table-content">
                                                    <tbody>
                                                        @foreach ($receipt->receiptDetail as $orderProduct)
                                                            @php
                                                                $products[] = $orderProduct;
                                                                $quantity = QuantityProductInStock::where(['product_id' => $orderProduct->product_id, 'stock_id' => $receipt->warehouse_id])?->first()?->quantity;
                                                            @endphp
                                                            <tr class="item__product">
                                                                <td class="vertical-align-t" style="margin:20px">
                                                                    <div class="wrap-img">
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            width="100px" height="100px"
                                                                            src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                    </div>
                                                                </td>
                                                                <td class="pl5 p-r5 " width = "30%">
                                                                    {{ $orderProduct->product_name }}
                                                                    <br> (Màu: {{ $orderProduct->color }} - Size:
                                                                    {{ $orderProduct->size }} )
                                                                </td>
                                                                <td class="pl5 p-r5 " width = "30%">
                                                                    SKU: {{ $orderProduct->sku }}
                                                                </td>
                                                                <td class="pl5 p-r5  text-end" width="25%">
                                                                    <span style="white-space: nowrap;">Số
                                                                        lượng:
                                                                        {{ $orderProduct->quantity }}
                                                                        sản phẩm</span>
                                                                </td>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        <tr>
                                                            <td colspan="5" class="text-end h5">
                                                                <h3> <strong>Tổng số lượng: </strong> <span
                                                                        class="widget__amount">{{ $receipt->quantity }} sản
                                                                        phẩm</span>
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
                                                                disabled="">{{ $receipt->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3">
                                                            <label class="text-title-field">Ngày dự kiến</label>
                                                            @php
                                                                $inputDate = $receipt->expected_date;
                                                                $formattedDate = date('d-m-Y', strtotime($inputDate));
                                                            @endphp
                                                            <input class="form-control flatpickr-input" data-date-format="d-m-Y"
                                                                v-pre="1" placeholder="d-m-Y" data-input=""
                                                                readonly="readonly" name="expected_date" type="text"
                                                                value="{{ $formattedDate }}" id="expected_date"
                                                                aria-invalid="false" aria-describedby="expected_date-error"
                                                                disabled>

                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if (
                                    $receipt->status != \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::PENDING &&
                                        $receipt->status != \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::DENIED)
                                    <div class="card-footer text-center"
                                        style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal" data-bs-whatever="@mdo">
                                            In phiếu nhập kho
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                {{ trans('plugins/warehouse::bill_export.title_form_out') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="myForm" action="{{ route('hub-issue.export-file') }}" method="POST">
                                @csrf
                                <input type="text" class="form-control" id="id" value="{{ $receipt->id }}"
                                    name="id" hidden="hidden">
                                <input type="text" class="form-control" id="type_bill"
                                    value="{{ trans('plugins/warehouse::bill_export.title_form_out') }}" name="type_bill"
                                    hidden="hidden">
                                <div class="mb-3">
                                    <label for="recipient-name"
                                        class="col-form-label required">{{ trans('plugins/warehouse::bill_export.proposal_name') }}:</label>
                                    <input type="text" class="form-control" id="proposal_name"
                                        value="{{ $receipt->invoice_issuer_name }}" name="proposal_name">
                                </div>
                                <div class="mb-3">
                                    <label for="recipient-name"
                                        class="col-form-label required">{{ trans('plugins/warehouse::bill_export.receiver_name') }}:</label>
                                    <input type="text" class="form-control" id="receiver_name" name="receiver_name">
                                </div>
                                <div class="mb-3">
                                    <label for="recipient-name"
                                        class="col-form-label required">{{ trans('plugins/warehouse::bill_export.storekeeper_name') }}:</label>
                                    <input type="text" class="form-control" id="storekeeper_name" name="storekeeper_name"
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
                                    <input type="text" class="form-control" id="manager_name" name="manager_name">
                                </div>
                                <div class="mb-3">
                                    <label for="recipient-name"
                                        class="col-form-label required">{{ trans('plugins/warehouse::bill_export.today') }}:</label>
                                    <input type="date" class="form-control" id="today" value="{{ date('Y-m-d') }}"
                                        name="today">
                                </div>
                                <div style="float: right">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" id="print"
                                        name="button_type" value="print"> <i class="fas fa-print"></i>
                                        {{ __('In phiếu nhập kho') }}</button>
                                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" id="download"
                                        name="button_type" value="download"><i class="fas fa-download"></i>
                                        {{ __('Tải phiếu nhập kho') }}</button>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer"></div>
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
                                    <div class="pd-all-20">
                                        <div class="card-header flexbox-grid-default">
                                            <div class="w-75">
                                                <label $class="title-product-main text-no-bold">{{ __('Phiếu thực nhập') }}



                                                    </strong>
                                                </label>
                                                <div>
                                                    Người nhập kho:
                                                    <strong>{{ $receipt->invoice_issuer_name }}</strong>
                                                </div>
                                                <div>
                                                    Ngày nhập: {{ date('d-m-Y', strtotime($actual->created_at)) }}
                                                </div>
                                            </div>
                                            <span class="card-notify-year blue">Phiếu thực nhập</span>
                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="card meta-boxes mb-3" id="gallery_wrap">
                                            <div class="card-header">
                                                <h4 class="card-title">
                                                    Danh sách thực nhập kho
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="table-wrap">
                                            <table class="table-order table-divided table-vcenter card-table w-100">
                                                <tbody>
                                                    @php
                                                        $totalActual = 0;
                                                    @endphp
                                                    @foreach ($actual->actualDetail as $details)
                                                        @php
                                                            $totalActual += $details->quantity;
                                                        @endphp
                                                        <tr>
                                                            <td class="vertical-align-t" width="20%" style="margin:20px">
                                                                <div class="wrap-img">
                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                        width="100px" height="100px"
                                                                        src="{{ RvMedia::getImageUrl($details->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="image">
                                                                </div>
                                                            </td>
                                                            <td class="pl5 p-r5 text-center " width="30%">
                                                                {{ $details?->product_name }}
                                                                <br> (Màu: {{ $details->color }} - Size:
                                                                {{ $details->size }} )
                                                            </td>
                                                            <td class="pl5 p-r5 text-center " width="30%">
                                                                SKU : {{ $details?->sku }}
                                                            </td>
                                                            <td class="pl5 p-r5 text-center " width="30%">
                                                                Số lượng {{ $details?->quantity }} sản phẩm
                                                            </td>

                                                        </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="5" class="text-end h5">
                                                            <h3> <strong>Tổng số lượng: </strong> <span
                                                                    class="widget__amount">{{ $totalActual }} sản phẩm</span>
                                                            </h3>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
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

                                                        @if ($actual->image)
                                                            @php
                                                                $images = json_decode($actual->image);
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
    </div>
    <script>
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
