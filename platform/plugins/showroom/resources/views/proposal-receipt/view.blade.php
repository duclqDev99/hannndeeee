@extends(BaseHelper::getAdminMasterLayoutTemplate())

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

        #accordion .card-body {
            cursor: pointer;
        }

        .group-batch-detail {
            padding: 20px;
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
        $strCenter = 6;
        if ($proposal->proposalReceiptDetail[0]->quantity_submit == 0) {
            $strCenter = '12';
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
                        <h3 style="margin-top: 10px;">Mục đích nhập kho:
                            <strong>{{ $proposal->title }}</strong>
                        </h3>
                    </div>

                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho xuất:</label>
                            <strong>{{ $proposal->warehouseReceipt->showroom->hub->warehouseInHub->first()->name }}
                                -
                                {{ $proposal->warehouseReceipt->showroom->hub->name }}</strong>
                        </div>
                        <div class="info-group">
                            <label>Người đề xuất:</label>
                            <strong>{{ $proposal->invoice_issuer_name }}</strong>
                        </div>

                        <div>
                            Ngày tạo: {{ date('d/m/Y', strtotime($proposal->created_at)) }}
                        </div>

                        <div class="info-group">
                            <label>Mã đơn hàng:</label>
                            <strong>{{ $proposal->general_order_code ?: '—' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho nhận:</label>
                            <strong>{{ $proposal->warehouse_name }} -
                                {{ $proposal->warehouseReceipt->showroom->name }}</strong>

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
                @isset($proposal)
                    <div class="col-lg-{{ $strCenter }} col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">
                                            <div class="pd-all-20">
                                                <span class="card-notify-year red">Phiếu đề xuất xuất kho</span>

                                            </div>
                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                @php
                                                    $totalQty = 0;
                                                    $products = [];
                                                    $hasBatchProducts = false;
                                                    $hasNonBatchProducts = false;
                                                @endphp

                                                @foreach ($proposal->proposalReceiptDetail as $orderProduct)
                                                    @if ($orderProduct->batch_id)
                                                        @php
                                                            $hasBatchProducts = true;
                                                        @endphp
                                                    @else
                                                        @php
                                                            $hasNonBatchProducts = true;
                                                        @endphp
                                                    @endif
                                                @endforeach
                                                <div class="table-wrap">
                                                    @if ($hasBatchProducts)
                                                        <div class="col-md-12">
                                                            <h3 style="margin: 20px 0 0 15px">Nhập theo lô</h3>
                                                            <table
                                                                class="table-order table-divided table-vcenter card-table col-12">
                                                                <tbody>
                                                                    @foreach ($proposal->proposalReceiptDetail as $orderProduct)
                                                                        @if ($orderProduct->batch_id)
                                                                            <tr class="item__product">
                                                                                <td class="width-60-px min-width-60-px vertical-align-t"
                                                                                    width="20%">
                                                                                    @if ($orderProduct->id)
                                                                                        <div class="wrap-img">
                                                                                            <img style="margin-top: 20px"
                                                                                                class="thumb-image thumb-image-cartorderlist"
                                                                                                src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
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
                                                                                        $quantity = $orderProduct?->batch?->quantity;
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
                                                            {{-- <h3 style="margin: 20px 0 0 15px">Nhập lẻ</h3> --}}
                                                            <table
                                                                class="table-order table-divided table-vcenter card-table col-12">
                                                                <tbody>
                                                                    @foreach ($proposal->proposalReceiptDetail as $orderProduct)
                                                                        @if (!$orderProduct->batch_id)
                                                                            @php
                                                                                $total += $orderProduct->quantity;
                                                                            @endphp
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
                                                                                    <div>
                                                                                        (Màu
                                                                                        {{ $orderProduct->product->variationProductAttributes[0]->title ?? '---' }}
                                                                                        - Size
                                                                                        {{ $orderProduct->product->variationProductAttributes[1]->title ?? '---' }})
                                                                                    </div>
                                                                                </td>
                                                                                <td class="pl5 p-r5" width="30%">
                                                                                    <div class="inline_block">
                                                                                        <span>Sku:
                                                                                            <strong>{{ $orderProduct->sku }}</strong></span>
                                                                                    </div>
                                                                                </td>
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
                                                                                    class="check__highlight">{{ $total }}
                                                                                    sản
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
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3 w-100">
                                                            <label class="text-title-field">Ghi chú</label>
                                                            <textarea class="form-control textarea-auto-height" name="description" rows="4" placeholder="{{ __('Ghi chú') }}"
                                                                disabled>{{ $proposal->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $inputDate = $proposal->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
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
                        <div class="modal fade" id="printProposal" tabindex="-1" aria-labelledby="printProposal"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Phiếu đề xuất nhập kho</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="myForm" action="{{ route('proposal-showroom-receipt.export-file') }}"
                                            method="POST">
                                            @csrf
                                            <input type="text" class="form-control" id="id"
                                                value="{{ $proposal->id }}" name="id" hidden="hidden">
                                            <input type="text" class="form-control" id="type_bill"
                                                value="{{ trans('plugins/warehouse::bill_export.title_form_in') }}"
                                                name="type_bill" hidden="hidden">
                                            <div class="mb-3">
                                                <label for="recipient-name"
                                                    class="col-form-label required">{{ __('Người lập biểu') }}:</label>
                                                <input type="text" class="form-control" id="proposal_name"
                                                    value="{{ $proposal->invoice_issuer_name }}" name="proposal_name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="recipient-name"
                                                    class="col-form-label required">{{ __('Kế toán hoặc trưởng bộ phận') }}:</label>
                                                <input type="text" class="form-control" id="receiver_name"
                                                    name="receiver_name" required>
                                            </div>
                                            <div style="float: right">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger" data-bs-dismiss="modal"
                                                    id="print" name="button_type" value="print"> <i
                                                        class="fas fa-print"></i>
                                                    {{ __('In phiếu đề xuất') }}</button>
                                                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal"
                                                    id="download" name="button_type" value="download"><i
                                                        class="fas fa-download"></i>
                                                    {{ __('Tải phiếu đề xuất') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endisset
                @if ($proposal->proposalReceiptDetail[0]->quantity_submit > 0)
                    <div class="col-lg-6 col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">


                                            <span class="card-notify-year blue">Phiếu duyệt</span>
                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <div class="table-wrap">
                                                    <table
                                                        class="table-order table-divided table-vcenter card-table w-100">
                                                        <tbody>
                                                            @php
                                                                $totalQty = 0;
                                                            @endphp
                                                            @foreach ($proposal->proposalReceiptDetail as $orderProduct)
                                                                <tr class="item__product">
                                                                    <td
                                                                        class="width-60-px min-width-60-px vertical-align-t">
                                                                        @if ($orderProduct->product_id !== 0)
                                                                            <div class="wrap-img">
                                                                                <img class="thumb-image thumb-image-cartorderlist"
                                                                                    style="max-width: 100px; margin-top: 20px"
                                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                    alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                    <td class="p-3 min-width-200-px">
                                                                        {{ $orderProduct->product_name }}
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
                                                                            <span>Sku:
                                                                                <strong>{{ $orderProduct->sku }}</strong></span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="p-3" width="120px"
                                                                        style="white-space: nowrap;">
                                                                        <span style="white-space: nowrap;">Số lượng:</span>
                                                                        <span
                                                                            class="check__highlight">{{ $orderProduct->quantity_submit }}
                                                                        </span>sản phẩm
                                                                    </td>
                                                                </tr>
                                                                @php
                                                                    $totalQty += $orderProduct->quantity_submit;
                                                                @endphp
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="5" class="text-end h3">
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
                                                            <label class="text-title-field">Ghi chú</label>
                                                            <textarea class="form-control textarea-auto-height" name="description" rows="4"
                                                                placeholder="{{ __('Ghi chú') }}" disabled>{{ $proposal->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $inputDate = $proposal->expected_date_submit; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                                        $formattedDate = date('d/m/Y', strtotime($inputDate));
                                                    @endphp
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3">
                                                            <label
                                                                class="text-title-field">{{ __('Ngày dự kiến') }}</label>
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
                @endif
            </div>
            <div class="card-footer text-center"
                style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#printProposal"
                    data-bs-whatever="@mdo">
                    {{ __('In phiếu đề xuất') }}
                </button>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const widget = document.querySelectorAll('.widget__view .ui-layout__item');

            const typeCheckHighlight = document.querySelector('#type_check_highlight');

            if (typeCheckHighlight && typeCheckHighlight.value != 'warehouse') {
                if (widget && widget.length === 2) {
                    const wp_porposal = widget[0];
                    const wp_receipt = widget[1];

                    const itemHighlightPorposal = wp_porposal.querySelectorAll('.check__highlight');
                    const itemHighlightReceipt = wp_receipt.querySelectorAll('.check__highlight');

                    for (let index = 0; index < itemHighlightPorposal.length; index++) {
                        console.log('text contetn: ', itemHighlightPorposal[index].textContent,
                            itemHighlightReceipt[index].textContent);
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
            }
        })
    </script>
@stop
