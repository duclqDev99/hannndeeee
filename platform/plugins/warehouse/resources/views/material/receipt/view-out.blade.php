@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\Warehouse\Enums\MaterialReceiptStatusEnum;
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

        .thumb-image-cartorderlist {
            margin-top: 20px;
            -webkit-border-radius: 20px;
            -moz-border-radius: 20px;
            border-radius: 20px;
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
                                        <div class="card-header flexbox-grid-default">
                                            <div class="flexbox-auto-right mr5">
                                                <label $class="title-product-main text-no-bold">{{ __('Phiếu xuất kho') }}
                                                    <strong>{{ $receipt->proposal_code }}</strong> -
                                                    Xuất từ: <strong>{{ $receipt->warehouse_name }} <i
                                                            class="fa-solid fa-arrow-right"></i>
                                                        {{ $receipt->proposal->warehouse->name }}</strong>
                                                </label>
                                                <div>
                                                    Mã đơn hàng: <strong>{{ $receipt->general_order_code }}</strong>
                                                </div>
                                                <div>
                                                    Người xác nhận: <strong>{{ $receipt->invoice_issuer_name }}</strong>
                                                </div>
                                                <div>
                                                    Tiêu đề: {{ $receipt->title }}
                                                </div>
                                            </div>
                                            <span class="card-notify-year blue">Phiếu xuất</span>

                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="table-wrap">
                                            <table class="table-order table-divided table-vcenter card-table">
                                                <tbody>
                                                    @php
                                                        $totalPriceReceipt = 0;
                                                        $totalQuantity = 0;
                                                    @endphp
                                                    @foreach ($receipt->proposalOutDetail as $orderProduct)
                                                        <tr class="item__product">
                                                            <td class="vertical-align-t" width="35%">
                                                                @if ($orderProduct->material_code !== 0)
                                                                    <div class="wrap-img">
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            width="100px"
                                                                            src="{{ RvMedia::getImageUrl($orderProduct->material($orderProduct->material_code)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            alt="{{ $orderProduct->material($orderProduct->material_code)->first()?->name }}">
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="pl5 p-r5 min-width-200-px" width="20%">
                                                                {{ $orderProduct->material_name }}
                                                            </td>

                                                            <td class="pl5 p-r5" width="20%">
                                                                Mã nguyên liệu:
                                                                <strong>{{ $orderProduct->material_code }}</strong></span>
                                                            </td>


                                                            <td class="pl5 text-end" width="20%">
                                                                Số lượng: <span
                                                                    class="check__highlight">{{ $orderProduct->material_quantity }}</span>
                                                            </td>
                                                        </tr>

                                                        @php
                                                            $totalQuantity += $orderProduct->material_quantity;
                                                            $totalPriceReceipt += $orderProduct->material_price * $orderProduct->material_quantity;
                                                        @endphp
                                                    @endforeach

                                                    <tr>
                                                        <td colspan="6" class="text-end h5">
                                                            <strong>Tổng số lượng: </strong> <span
                                                                class="check__highlight">{{ $totalQuantity }}</span>
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
                                @if ($receipt->status == 'confirm')
                                    <div class="card-footer text-center"
                                        style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal" data-bs-whatever="@mdo">
                                            {{ trans('plugins/ecommerce::invoice.download') }}
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
                            <form id="myForm" action="{{ route('goods-issue-receipt.issue.export-receipt') }}"
                                method="POST">
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
                                        {{ trans('plugins/ecommerce::invoice.print') }}</button>
                                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" id="download"
                                        name="button_type" value="download"><i class="fas fa-download"></i>
                                        {{ trans('plugins/ecommerce::invoice.download') }}</button>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer"></div>
                    </div>
                </div>
            </div>
        @endisset
        @isset($receipt->proposalOutDetail[0]->actualBatchMaterial)

            <div class="col-lg-6 col-md-12">

                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item receipt">

                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="card-header flexbox-grid-default">
                                            <div class="flexbox-auto-right mr5">
                                                <label $class="title-product-main text-no-bold">{{ __('Phiếu xuất kho') }}
                                                    <strong>{{ $receipt->proposal_code }}</strong> -
                                                    Xuất từ: <strong>{{ $receipt->warehouse_name }} <i
                                                            class="fa-solid fa-arrow-right"></i>
                                                        {{ $receipt->proposal->warehouse->name }}</strong>
                                                </label>
                                                <div>
                                                    Mã đơn hàng: <strong>{{ $receipt->general_order_code }}</strong>
                                                </div>
                                                <div>
                                                    Người xác nhận thực xuất:
                                                    <strong>{{ $receipt->invoice_confirm_name }}</strong>
                                                </div>
                                                <div>
                                                    Tiêu đề: {{ $receipt->title }}
                                                </div>
                                                <span class="card-notify-year red">Phiếu thực xuất</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="table-wrap">
                                            <table class="table-order table-divided table-vcenter card-table">
                                                <tbody>
                                                    @php
                                                        $totalQty = 0;
                                                    @endphp
                                                    @foreach ($receipt->proposalOutDetail as $orderMaterial)
                                                        <tr class="item__product">
                                                            <td class="vertical-align-t" width="35%">
                                                                @if ($orderMaterial->material_code !== 0)
                                                                    <div class="wrap-img">
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            width="100px"
                                                                            src="{{ RvMedia::getImageUrl($orderMaterial->material($orderMaterial->material_code)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            alt="{{ $orderMaterial->material($orderMaterial->material_code)->first()?->name }}">
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="pl5 p-r5 min-width-200-px" width="20%">
                                                                {{ $orderMaterial->material_name }}
                                                            </td>
                                                            <td class="pl5 p-r5" width="20%">
                                                                <div class="inline_block">
                                                                    <span>Mã nguyên liệu:
                                                                        <strong>{{ $orderMaterial->material_code }}</strong></span>
                                                                </div>
                                                            </td>

                                                            <td class="pl5 text-end" width="20%">
                                                                Số lượng: <span
                                                                    class="check__highlight">{{ $orderMaterial->actualBatchMaterial->quantity }}</span>
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="padding: 0;">
                                                                <div style="width: 50%; float: right;">
                                                                    <table class="table table-bordered" id="table-add"
                                                                        style="width: 100%;">
                                                                        @foreach ($orderMaterial->actualBatchMaterial->autualDetail as $actual)
                                                                            <tr>
                                                                                <td>{{ $actual->batch_code }}</td>
                                                                                <td>

                                                                                    <span>Số lượng: {{ $actual->quantity }}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </table>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @isset($orderMaterial->actualBatchMaterial->reason)
                                                            <tr class="item__product">
                                                                <td>
                                                                    <label>Lý do thay đổi: </label>
                                                                </td>
                                                                <td colspan="3">
                                                                    <textarea class="form-control" rows="1" disabled placeholder="Vui lòng ghi rõ lý do">{{ $orderMaterial->actualBatchMaterial->reason }}</textarea>
                                                                </td>
                                                            </tr>
                                                        @endisset


                                                        @php
                                                            $totalQty += $orderMaterial->actualBatchMaterial->quantity;
                                                        @endphp
                                                    @endforeach


                                                    <tr>
                                                        <td colspan="6" class="text-end h5">
                                                            <strong>Tổng số lượng: </strong> <span class="check__highlight">
                                                                {{ $totalQty }}</span>
                                                        </td>

                                                    </tr>
                                                </tbody>

                                            </table>

                                        </div>
                                    </div>
                                    <div class="card-body pd-all-20 p-none-t">
                                        <div class="flexbox-grid-default">
                                            <div class="flexbox-auto-right pl5">
                                                <div class="row">
                                                    <div class="col-lg-6 col-sm-12">
                                                        <div class="py-3">
                                                            <label class="text-title-field">{{ __('Ngày xác nhận') }}</label>
                                                            <input type="date" class="form-control" name="date_confirm"
                                                                value="{{ $receipt->date_confirm }}" disabled>
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
