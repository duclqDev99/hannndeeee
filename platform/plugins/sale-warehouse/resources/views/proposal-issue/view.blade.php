@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
    use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
    use Botble\HubWarehouse\Models\Warehouse;
    use Botble\HubWarehouse\Models\QuantityProductInStock;
    use Botble\ProductQrcode\Enums\QRStatusEnum;
    use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;

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

        /* Định nghĩa các lớp cụ thể */
    </style>
    @php
        $strCenter = 12;
        if (isset($receipt)) {
            $strCenter = 6;
        }
    @endphp
    <div class="widget__view row row-cards  justify-content-center
    ">
        <div class="card col-9">
            <div class="card-header">
                <div>
                    <h2 class="title">Thông tin đơn đề xuất xuất kho
                        {{ BaseHelper::clean(get_proposal_issue_product_code($proposal->proposal_code)) }}
                        <span class="status-container status-tag status-{{ $proposal->status }}">
                            @php
                                echo $proposal->status->toHtml();
                            @endphp

                        </span>
                    </h2>
                    <h3>
                        Mục đích nhập kho:
                        {{ $proposal->title }}
                    </h3>
                </div>

            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho xuất:</label>
                            <strong>{{ $proposal->warehouse_name }} -
                                {{ $proposal->warehouseIssue->saleWarehouse->name }}</strong>
                        </div>


                        <div class="info-group">
                            <label>Người đề xuất:</label>
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
                            @if ($proposal->is_warehouse == 'tour')
                                                            Xuất đi giải
                                                        @endif</strong>
                        </div>
                        <div>
                            Người duyệt: <strong>{{ $proposal->invoice_confirm_name ?: '—' }}</strong>
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


                                            <span class="card-notify-year red">Phiếu đề xuất xuất kho</span>

                                        </div>
                                        @php
                                            $totalQty = 0;
                                            $products = [];
                                            $hasBatchProducts = false;
                                            $hasNonBatchProducts = false;
                                        @endphp

                                        @foreach ($proposal->proposalHubIssueDetail as $orderProduct)
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
                                        <div class="pd-all-20 p-none-t border-top-title-main">
                                            <div class="table-wrap">
                                                @if ($hasBatchProducts)
                                                    <div class="col-md-12">
                                                        <h3 style="margin: 20px 0 0 15px">Xuất theo lô</h3>
                                                        <table
                                                            class="table-order table-divided table-vcenter card-table col-12">
                                                            <tbody>
                                                                @foreach ($proposal->proposalHubIssueDetail as $orderProduct)
                                                                    @if ($orderProduct->is_batch == 1)
                                                                        <tr class="item__product">
                                                                            <td class="width-60-px min-width-60-px vertical-align-t"
                                                                                width="20%">
                                                                                @if ($orderProduct->id)
                                                                                    <div class="wrap-img">
                                                                                        <img style="margin-top: 20px"
                                                                                            class="thumb-image thumb-image-cartorderlist"
                                                                                            src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                            alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}"
                                                                                            width="100px" height="100px">
                                                                                    </div>
                                                                                @endif
                                                                            </td>
                                                                            <td class="pl5 p-r5" width="30%">
                                                                                {{ $orderProduct->product_name }}
                                                                            </td>

                                                                            <td class="pl5 p-r5" width="30%">
                                                                                @php
                                                                                    $quantity =
                                                                                        $orderProduct?->batch
                                                                                            ?->quantity;
                                                                                @endphp
                                                                                Lô:
                                                                                {{ $orderProduct?->batch?->batch_code }}

                                                                            </td>
                                                                            <td class="pl5 p-r5" width="20%">
                                                                                Số lượng trong lô:
                                                                                {{ $orderProduct->quantity }} sản phẩm
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                                @php
                                                    $total = 0;
                                                @endphp
                                                @if ($hasNonBatchProducts)
                                                    <div class="col-md-12">
                                                        {{-- <h3 style="margin: 20px 0 0 15px">Xuất lẻ</h3> --}}
                                                        <table
                                                            class="table-order table-divided table-vcenter card-table col-12">
                                                            <tbody>
                                                                @foreach ($proposal->proposalHubIssueDetail as $orderProduct)
                                                                    @if ($orderProduct->is_batch == 0)
                                                                        <tr class="item__product">
                                                                            <td class="width-60-px min-width-60-px vertical-align-t"
                                                                                width="20%">
                                                                                @if ($orderProduct->id)
                                                                                    <div class="wrap-img">
                                                                                        <img style="margin-top: 20px"
                                                                                            class="thumb-image thumb-image-cartorderlist"
                                                                                            src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                            alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}"
                                                                                            width="100px" height="100px">
                                                                                    </div>
                                                                                @endif
                                                                            </td>
                                                                            <td class="pl5 p-r5" width="30%">
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


                                                                            </td>
                                                                            <td class="pl5 p-r5" width="30%">
                                                                                <div class="inline_block">
                                                                                    <span>SKU:
                                                                                        <strong>{{ $orderProduct->product->sku }}</strong></span>
                                                                                </div>
                                                                            </td>
                                                                            @php
                                                                                $total += $orderProduct->quantity;
                                                                            @endphp
                                                                            <td class="pl5 p-r5" width="20%">
                                                                                Đề xuất : {{ $orderProduct->quantity }} sản
                                                                                phẩm
                                                                            </td>

                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                                <tr>
                                                                    <td colspan="6" class="text-end h5">
                                                                        <h3>
                                                                            <strong>Tổng số lượng đề xuất: </strong> <span
                                                                                class="">{{ $total }} sản
                                                                                phẩm</span>
                                                                        </h3>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif


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
                                                            <label class="text-title-field">Ngày dự kiến</label>
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
                                                        action="{{ route('proposal-hub-issue.export-file', $proposal->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <input type="text" class="form-control" id="id"
                                                            value="{{ $proposal->id }}" name="id" hidden="hidden">

                                                        <div class="mb-3">
                                                            <label for="proposal_name"
                                                                class="col-form-label required">Người
                                                                đề
                                                                xuất:</label>
                                                            <input type="text" class="form-control" id="proposal_name"
                                                                readonly value="{{ $proposal->invoice_issuer_name }}"
                                                                name="proposal_name">
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
                                                                {{ __('In phiếu đề xuất') }}</button>
                                                            {{-- <button type="submit" class="btn btn-primary"
                                                        data-bs-dismiss="modal" id="download" name="button_type"
                                                        value="download"><i class="fas fa-download"></i>
                                                        {{ trans('plugins/ecommerce::invoice.download') }}</button> --}}
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
                                            <span class="card-notify-year blue">Phiếu duyệt</span>


                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <div class="table-wrap">
                                                    <table class="table-order table-divided table-vcenter card-table col-12">
                                                        <tbody>
                                                            @php
                                                                $totalQuantityReceipt = 0;
                                                            @endphp
                                                            @foreach ($receipt->productIssueDetail as $orderProduct)
                                                                <tr class="item__product">

                                                                    <td class="width-60-px min-width-60-px vertical-align-t"
                                                                        width="20%">
                                                                        <div class="wrap-img">
                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                style="max-width: 100px;"
                                                                                src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                        </div>
                                                                    </td>
                                                                    <td class="pl5 p-r5 " width="30%">
                                                                        {{ $orderProduct->product_name }}
                                                                        <div>
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
                                                                    </td>
                                                                    <td class="pl5 p-r5 " width= "30%">
                                                                        <div class="inline_block">
                                                                            <span> SKU:
                                                                                <strong>{{ $orderProduct->product->sku }}</strong></span>
                                                                        </div>
                                                                    </td>

                                                                    <td class="pl5 p-r5 " width="20%">
                                                                        Số lượng:
                                                                        <span
                                                                            class="check__highlight">{{ $orderProduct->quantity }}</span>
                                                                        sản phẩm

                                                                    </td>
                                                                    @php
                                                                        $totalQuantityReceipt +=
                                                                            $orderProduct->quantity;
                                                                    @endphp

                                                                </tr>
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="12" class="text-end h5">
                                                                    <h3>

                                                                        <strong>Tổng số lượng: </strong> <span
                                                                            class="widget__tax__amount">{{ $totalQuantityReceipt }}
                                                                            sản phẩm</span>
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
                                                                <label class="text-title-field">Ngày dự kiến</label>
                                                                @php
                                                                    $inputDate = $receipt->expected_date;
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
                @endisset
            </div>
            <div class="card-footer text-center"
                style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal"
                    data-bs-whatever="@mdo">
                    In phiếu đề xuất
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
