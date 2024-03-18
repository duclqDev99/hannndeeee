@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
    use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
    use Botble\HubWarehouse\Models\Warehouse;
    use Botble\HubWarehouse\Models\QuantityProductInStock;
    use Botble\ProductQrcode\Enums\QRStatusEnum;
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

        .thumb-image-cartorderlist {
            margin-top: 20px;
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
        if (isset($receipt)) {
            $strCenter = 6;
        }
    @endphp
    <div class="widget__view row row-cards justify-content-center
    ">
        <div class="card col-10">
            <div class="card-header">
                <div>
                    <h2 class="title">Thông tin đơn đề xuất nhập kho
                        {{ BaseHelper::clean(get_proposal_receipt_product_code($proposal->proposal_code)) }}
                        <span class="status-container status-tag status-{{ $proposal->status }}">
                            @php
                                echo $proposal->status->toHtml();
                            @endphp

                        </span>
                    </h2>
                    <div>
                        <h3>
                            Mục đích xuất kho: {{ $proposal->title }}
                        </h3>
                    </div>
                </div>

            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho xuất:</label>
                            <strong> @php
                                $isSameWarehouse = $proposal->warehouse_receipt_id == $proposal->warehouse_id;
                                $isWarehouseType = $proposal->warehouse_type == Warehouse::class;
                            @endphp

                                @if ($isSameWarehouse && $isWarehouseType)
                                    Nhập hàng
                                    tồn
                                @else
                                    {{ $proposal->warehouse->hub?->name
                                        ? $proposal->warehouse->name . ' - ' . $proposal->warehouse->hub->name
                                        : $proposal->warehouse->name }}
                                @endif
                            </strong>
                        </div>
                        <div>
                            Người đề xuất:
                            <strong>{{ $proposal->invoice_issuer_name }}</strong>
                        </div>
                        <div class="info-group">
                            Ngày tạo: {{ date('d-m-Y', strtotime($proposal->created_at)) }}
                        </div>
                        <div class="info-group">
                            <label>Mã đơn hàng:</label>
                            <strong>{{ $proposal->general_order_code ?: '—' }}</strong>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho nhận:</label>
                            <strong>{{ $proposal->warehouseReceipt->name . ' - ' . $proposal->warehouseReceipt->hub->name }}</strong>
                        </div>
                        <div>
                            Người duyệt:
                            <strong>{{ $proposal->invoice_confirm_name ?: '—' }}</strong>
                        </div>
                        <div>
                            Ngày duyệt:
                            {{ $proposal->date_confirm ? date('d-m-Y', strtotime($proposal->date_confirm)) : '—' }}
                        </div>

                        @if ($proposal->reason_cancel)
                            <div> Lý do từ chối: <strong style="color: red">
                                    {{ $proposal->reason_cancel }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body row">
                <div class="col-lg-{{ $strCenter }} col-md-12">
                    <div class="ui-layout">
                        <div class="flexbox-layout-sections">
                            <div class="flexbox-layout-section-primary mt20">
                                <div class="card ui-layout__item">
                                    <div class="wrapper-content">
                                        <div class="pd-all-20">

                                            <span class="card-notify-year red">Phiếu đề xuất nhập kho</span>

                                        </div>
                                        <div class="pd-all-20 p-none-t border-top-title-main">
                                            <div class="table-wrap">
                                                <table class="table-order table-divided table-vcenter card-table col-12">
                                                    <tbody>
                                                        @foreach ($proposal->proposalReceiptDetail as $orderProduct)
                                                            <tr class="item__product">
                                                                <td class="vertical-align-t" width="15%">
                                                                    <div class="wrap-img">
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            style="max-width: 100px;"
                                                                            src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                            alt="{{ $orderProduct->product($orderProduct->id)->first()?->name }}">
                                                                    </div>
                                                                </td>

                                                                <td class="pl5 p-r5 text-center">
                                                                    {{ $orderProduct->product_name }}
                                                                    <div>
                                                                        @foreach ($orderProduct?->product?->variationProductAttributes as $attribute)
                                                                            @if ($attribute?->color)
                                                                                {{ 'Màu: ' . $attribute?->title }}
                                                                            @endif
                                                                        @endforeach

                                                                        @foreach ($orderProduct?->product?->variationProductAttributes as $attribute)
                                                                            @if (!$attribute?->color)
                                                                                {{ 'Size: ' . $attribute?->title }}
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                </td>

                                                                <td class="pl5 p-r5 text-center" width= "25%">
                                                                    <div class="inline_block">
                                                                        <span> SKU:
                                                                            <strong>{{ $orderProduct->sku }}</strong>
                                                                        </span>
                                                                    </div>
                                                                </td>


                                                                <td class="pl5 p-r5 text-end" width="20%">
                                                                    Số lượng
                                                                    <span
                                                                        class="check__highlight">{{ $orderProduct->quantity }}</span>
                                                                    sản phẩm
                                                                    {{-- @if ($isSameWarehouse && $isWarehouseType)
                                                                SP
                                                            @else
                                                                lô
                                                            @endif --}}
                                                                </td>

                                                            </tr>
                                                        @endforeach
                                                        <tr>
                                                            <td colspan="12" class="text-end h5">
                                                                <h3>

                                                                    <strong>Tổng số lượng: </strong> <span
                                                                        class="check__highlight">{{ $proposal->quantity }}</span>
                                                                    sản phẩm
                                                                    {{-- @if ($isSameWarehouse && $isWarehouseType)
                                                                SP
                                                            @else
                                                                lô
                                                            @endif --}}
                                                                </h3>
                                                            </td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-body pd-all-20 p-none-t">
                                            <div class="flexbox-grid-default">
                                                <div class="row">

                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3 w-100">
                                                            <label class="text-title-field">Ghi chú</label>
                                                            <textarea class="form-control textarea-auto-height" name="description" rows="4" placeholder="Ghi chú"
                                                                disabled="">{{ $proposal->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3">
                                                            <label class="text-title-field">Ngày dự kiến đề xuất</label>
                                                            @php
                                                                $inputDate = $proposal->expected_date;
                                                                $formattedDate = date('d-m-Y', strtotime($inputDate));
                                                            @endphp
                                                            <input class="form-control flatpickr-input"
                                                                data-date-format="d-m-Y" v-pre="1" placeholder="d-m-Y"
                                                                data-input="" readonly="readonly" name="expected_date"
                                                                type="text" value="{{ $formattedDate }}"
                                                                id="expected_date" aria-invalid="false"
                                                                aria-describedby="expected_date-error" disabled>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="exampleModal" tabindex="-1"
                                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">
                                                        {{ trans('Phiếu đề nghị xuất kho') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="myForm"
                                                        action="{{ route('proposal-hub-receipt.export-file', $proposal->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <input type="text" class="form-control" id="id"
                                                            value="{{ $proposal->id }}" name="id" hidden="hidden">

                                                        <div class="mb-3">
                                                            <label for="proposal_name" class="col-form-label required">Người
                                                                đề
                                                                xuất:</label>
                                                            <input type="text" class="form-control" id="proposal_name"
                                                                readonly value="{{ $proposal->invoice_issuer_name }}"
                                                                name="proposal_name">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="chief_department"
                                                                class="col-form-label required">Trưởng
                                                                bộ phận:</label>
                                                            <input type="text" class="form-control"
                                                                id="chief_department" name="chief_department"
                                                                placeholder="Nhập tên">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="warehouse_name"
                                                                class="col-form-label required">Kho
                                                                xuất:</label>
                                                            <input type="text" class="form-control"
                                                                id="warehouse_name" readonly name="warehouse_name"
                                                                value="{{ $proposal->warehouse_name }}">
                                                        </div>

                                                        <div style="float: right">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-danger"
                                                                data-bs-dismiss="modal" id="print" name="button_type"
                                                                value="print"> <i class="fas fa-print"></i>
                                                                {{ trans('In đề xuất nhập kho') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @isset($receipt)
                    <div class="col-lg-6 col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">
                                            <span class="card-notify-year blue">Phiếu duyệt đề xuất nhập kho</span>
                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <div class="table-wrap">
                                                    <table class="table-order table-divided table-vcenter card-table">
                                                        <tbody>
                                                            @php
                                                                $totalPriceReceipt = 0;
                                                            @endphp
                                                            @foreach ($receipt->receiptDetail as $orderProduct)
                                                                <tr class="item__product">

                                                                    <td class="vertical-align-t" width="15%">
                                                                        @if ($orderProduct->id)
                                                                            <div class="wrap-img">
                                                                                <img class="thumb-image thumb-image-cartorderlist"
                                                                                    style="max-width: 100px;"
                                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                    alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                    <td class="pl5 p-r5 text-center">
                                                                        {{ $orderProduct->product_name }}
                                                                        <div>
                                                                            @foreach ($orderProduct?->product?->variationProductAttributes as $attribute)
                                                                                    @if ($attribute?->color)
                                                                                        {{ 'Màu: ' . $attribute?->title }}
                                                                                    @endif
                                                                                @endforeach

                                                                                @foreach ($orderProduct?->product?->variationProductAttributes as $attribute)
                                                                                    @if (!$attribute?->color)
                                                                                        {{ 'Size: ' . $attribute?->title }}
                                                                                    @endif
                                                                                @endforeach
                                                                        </div>
                                                                    </td>
                                                                    <td class="pl5 p-r5 text-center" width= "25%">
                                                                        <div class="inline_block">
                                                                            <span> SKU:
                                                                                <strong>{{ $orderProduct->sku }}</strong></span>&nbsp&nbsp&nbsp
                                                                        </div>
                                                                    </td>


                                                                    <td class="pl5 p-r5 text-end" width="20%">
                                                                        Số lượng
                                                                        <span
                                                                            class="check__highlight">{{ $orderProduct->quantity }}</span>
                                                                        sản phẩm

                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="12" class="text-end h5">
                                                                    <h3>

                                                                        <strong>Tổng số lượng: </strong> <span
                                                                            class="check__highlight">{{ $receipt->quantity }}</span>
                                                                        sản phẩm

                                                                    </h3>
                                                                </td>
                                                            </tr>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="card-body pd-all-20 p-none-t">
                                                <div class="flexbox-grid-default">
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
                                                                <label class="text-title-field">Ngày dự kiến duyệt</label>
                                                                @php
                                                                    $inputDate = $receipt->expected_date;
                                                                    $formattedDate = date('d-m-Y', strtotime($inputDate));
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
                @endisset
            </div>
            <div class="card-footer text-center"
                style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal"
                    data-bs-whatever="@mdo">
                    {{ trans('In đề xuất nhập kho') }}
                </button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const widget = document.querySelectorAll('.widget__view .ui-layout__item');

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
