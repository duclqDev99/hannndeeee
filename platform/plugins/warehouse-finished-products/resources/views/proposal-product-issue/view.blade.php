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
    </style>
    @php
        $strCenter = '';
        if (isset($proposal->expect_date_examine) || empty($proposal->expect_date_examine)) {
            $strCenter = 'justify-content-center';
        }
    @endphp
    <div class="widget__view row row-cards
    {{ $strCenter }}">
        <div class="col-lg-6 col-md-12">
            <div class="ui-layout">
                <div class="flexbox-layout-sections">
                    <div class="flexbox-layout-section-primary mt20">
                        <div class="card ui-layout__item">
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="card-header flexbox-grid-default">
                                        <div class="flexbox-auto-right mr5">
                                            <label
                                                class="title-product-main text-no-bold">{{ __('Phiếu đề xuất xuất kho') }}
                                                <strong>{{ $proposal->proposal_code }}</strong> -
                                                Xuất từ: <strong>{{ $proposal->warehouse_name }}
                                                    <i class="fa-solid fa-arrow-right"></i>
                                                    {{ $proposal?->warehouse->name }}
                                                    {{ optional($proposal->warehouse->hub)->name ? ' (' . $proposal?->warehouse->hub->name . ')' : '' }}

                                                </strong>
                                            </label>
                                            <div>
                                                Mã đơn hàng: <strong>{{ $proposal->general_order_code }}</strong>
                                            </div>
                                            <div>
                                                Người đề xuất: <strong>{{ $proposal->invoice_issuer_name }}</strong>
                                            </div>
                                            <div>
                                                Tiêu đề: {{ $proposal->title }}
                                            </div>
                                            @if ($proposal->status == 'denied')
                                                <div> Người từ chối xuất kho: <strong style="color: red">
                                                        {{ $proposal->invoice_confirm_name }}</strong></div>
                                                <div> Lý do từ chối: <strong style="color: red">
                                                        {{ $proposal->reason }}</strong></div>
                                            @endif
                                        </div>
                                        @if ($proposal->status == 'approved' || $proposal->status == 'confirm')
                                            <span class="card-notify-year red">Phiếu đề xuất</span>
                                        @elseif($proposal->status == 'denied')
                                            <span class="card-notify-year red">Phiếu đề xuất đã từ chối</span>
                                        @else
                                            <span class="card-notify-year blue">Phiếu đề xuất đang chờ duyệt</span>
                                        @endif

                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided table-vcenter card-table">
                                            <tbody>
                                                @php
                                                    $totalPrice = 0;
                                                @endphp
                                                @foreach ($proposal->proposalProductIssueDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t"width="15%">
                                                            <div class="wrap-img">
                                                                <img class="thumb-image thumb-image-cartorderlist"
                                                                    style="max-width: 100px;"
                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                    alt="{{ $orderProduct->product($orderProduct->id)->first()?->name }}">
                                                            </div>

                                                        </td>
                                                        <td class="pl5 p-r5" width= "20%">
                                                            {{ $orderProduct->product_name }}
                                                            <div>
                                                                {{ !empty($orderProduct->color) ? ' Màu: ' . $orderProduct->color : '' }}
                                                                {{ !empty($orderProduct->size) ? ' Size: ' . $orderProduct->size : '' }}
                                                            </div>
                                                        </td>

                                                        <td class="pl5 p-r5 text-center" width= "20%">
                                                            <div class="inline_block">
                                                                <span> Mã:
                                                                    <strong>{{ $orderProduct->sku }}</strong></span>
                                                            </div>
                                                        </td>
                                                        <td class="pl5 p-r5 text-center" width= "20%">
                                                            <div class="inline_block">
                                                                <span>@php echo $orderProduct->size === '' || $orderProduct->color === '' ? 'Xuất theo lô' : 'Xuất lẻ' @endphp</span>
                                                            </div>
                                                        </td>


                                                        <td class="pl5 p-r5 text-end" width="20%">
                                                            Dự kiến:
                                                            <span
                                                                class="check__highlight">{{ $orderProduct->quantity }}</span>
                                                            @php echo $orderProduct->size === '' || $orderProduct->color === '' ? 'Lô' : 'SP' @endphp
                                                        </td>

                                                    </tr>
                                                @endforeach
                                                {{-- <tr>
                                                    <td colspan="12" class="text-end h5">
                                                        <strong>Tổng số lượng: </strong> <span
                                                            class="widget__tax__amount">{{ $proposal->quantity }}</span>
                                                    </td>
                                                </tr> --}}
                                                <tr>
                                                    <td colspan="12" class="text-end h5">
                                                        <input type="number" name="ac_amount"
                                                            value="{{ $proposal->amount }}" class="ac_amount" hidden>

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
                                                    <label class="text-title-field">Ngày dự kiến</label>
                                                    @php
                                                        $inputDate = $proposal->expected_date;
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
                            <div class="card-footer text-center"
                                style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal" data-bs-whatever="@mdo">
                                    {{ trans('In phiếu đề xuất xuất kho') }}
                                </button>
                            </div>
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
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
                                                action="{{ route('proposal-product-issue.proposal.export', $proposal->id) }}"
                                                method="POST">
                                                @csrf
                                                <input type="text" class="form-control" id="id"
                                                    value="{{ $proposal->id }}" name="id" hidden="hidden">

                                                <div class="mb-3">
                                                    <label for="proposal_name" class="col-form-label required">Người đề
                                                        xuất:</label>
                                                    <input type="text" class="form-control" id="proposal_name"
                                                        readonly value="{{ $proposal->invoice_issuer_name }}"
                                                        name="proposal_name">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="warehouse_name" class="col-form-label required">Kho
                                                        xuất:</label>
                                                    <input type="text" class="form-control" id="warehouse_name"
                                                        readonly name="warehouse_name"
                                                        value="{{ $proposal->warehouse_name }}">
                                                </div>

                                                <div style="float: right">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger" data-bs-dismiss="modal"
                                                        id="print" name="button_type" value="print"> <i
                                                            class="fas fa-print"></i>
                                                        {{ trans('plugins/ecommerce::invoice.print') }}</button>
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
        @isset($proposal->expect_date_examine)
            <div class="col-lg-6 col-md-12">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="card-header flexbox-grid-default">
                                            <div class="flexbox-auto-right mr5">
                                                <label $class="title-product-main text-no-bold">{{ __('Phiếu xuất kho') }}
                                                    <strong>{{ $proposal->proposal_code }}</strong> -
                                                    Xuất từ: <strong>{{ $proposal->warehouse_name }}
                                                        <i class="fa-solid fa-arrow-right"></i>
                                                        {{ $proposal?->warehouse->name }}{{ optional($proposal->warehouse->hub)->name ? ' (' . $proposal?->warehouse->hub->name . ')' : '' }}

                                                    </strong>
                                                </label>
                                                <div>
                                                    Mã đơn hàng: <strong>{{ $proposal->general_order_code }}</strong>
                                                </div>
                                                <div>
                                                    Người xác nhận: <strong>{{ $proposal->invoice_confirm_name }}</strong>
                                                </div>
                                                <div>
                                                    Tiêu đề: {{ $proposal->title }}
                                                </div>
                                            </div>
                                            <span class="card-notify-year blue">Phiếu đã duyệt</span>
                                        </div>

                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="table-wrap">
                                            <table class="table-order table-divided table-vcenter card-table">
                                                <tbody>
                                                    @php
                                                        $totalPriceReceipt = 0;
                                                    @endphp
                                                    @foreach ($proposal->proposalProductIssueDetail as $orderProduct)
                                                        <tr class="item__product">

                                                            <td
                                                                class="width-60-px min-width-60-px vertical-align-t"width="15%">
                                                                <div class="wrap-img">
                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                        style="max-width: 100px;"
                                                                        src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                </div>
                                                            </td>
                                                            <td class="pl5 p-r5" width= "20%">
                                                                {{ $orderProduct->product_name }}
                                                                <div>
                                                                    {{ !empty($orderProduct->color) ? ' Màu: ' . $orderProduct->color : '' }}
                                                                    {{ !empty($orderProduct->size) ? ' Size: ' . $orderProduct->size : '' }}
                                                                </div>
                                                            </td>
                                                            <td class="pl5 p-r5 text-center" width= "20%">
                                                                <div class="inline_block">
                                                                    <span>Mã:
                                                                        <strong>{{ $orderProduct->sku }}</strong></span>
                                                                </div>
                                                            </td>

                                                            <td class="pl5 p-r5 text-center" width="12%">
                                                                Duyệt:
                                                                <span
                                                                    class="check__highlight">{{ $orderProduct->quantityExamine }}</span>
                                                                @php echo $orderProduct->size === '' || $orderProduct->color === '' ? 'Lô' : 'SP' @endphp

                                                            </td>

                                                        </tr>
                                                    @endforeach
                                                    {{-- <tr>
                                                        <td colspan="12" class="text-end h5">
                                                            <strong>Tổng số lượng: </strong> <span
                                                                class="widget__tax__amount">{{ $receipt->quantity }}</span>
                                                        </td>
                                                    </tr> --}}

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
                                                            disabled="">{{ $proposal->description_examine }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="py-3">
                                                        <label class="text-title-field">Ngày dự kiến</label>
                                                        @php
                                                            $inputDate = $proposal->expect_date_examine;
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
                        </div>
                    </div>
                </div>
            </div>
        @endisset
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
