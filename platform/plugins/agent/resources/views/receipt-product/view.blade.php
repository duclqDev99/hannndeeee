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
    </style>
    @php
        $strCenter = 6;
        if ($proposal->proposalReceiptDetail[0]->quantity_submit == 0) {
            $strCenter = 12;
        }
    @endphp
    <div class="widget__view row row-cards justify-content-center ">
        <div class="card col-10">
            <div class="card-header">
                <div>
                    <h2 class="title">Thông tin đơn đề xuất nhập kho
                        {{ BaseHelper::clean(get_proposal_receipt_product_code($proposal->proposal_code)) }}
                    </h2>
                    <div>
                        <h3>
                            Mục đích nhập kho: {{ $proposal->title }}
                        </h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho xuất:</label>
                            <strong>{{ $proposal->warehouseReceipt->agent->hub->warehouseInHub->first()->name }}
                                - {{ $proposal->warehouseReceipt->agent->hub->name }}</strong>
                        </div>
                        <div class="info-group">
                            <label>Người đề xuất:</label>
                            <strong>{{ $proposal->invoice_issuer_name }}</strong>
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
                                {{ $proposal->warehouseReceipt->agent->name }}</strong>

                        </div>


                        <div>
                            Ngày tạo: {{ date('d/m/Y', strtotime($proposal->created_at)) }}
                        </div>

                        @if ($proposal->reason_cancel)
                            <div> Lý do từ chối: <strong style="color: red">
                                    {{ $proposal->reason_cancel }}</strong></div>
                        @endif
                    </div>
                </div>
                <!-- Thêm các info-group khác tại đây -->
            </div>
            <div class="card-body row">
                @isset($proposal)
                    <div class="col-lg-{{ $strCenter }} col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">
                                            <span class="card-notify-year red">Phiếu đề xuất</span>

                                            <div class="pd-all-20 p-none-t border-top-title-main">
                                                <div class="table-wrap">
                                                    <table class="table-order table-divided table-vcenter card-table w-100">
                                                        <tbody>
                                                            @php
                                                                $totalQty = 0;
                                                            @endphp
                                                            @foreach ($proposal->proposalReceiptDetail as $orderProduct)
                                                                <tr class="item__product">
                                                                    <td class="width-60-px min-width-60-px vertical-align-t">
                                                                        <div class="wrap-img">
                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                style="max-width: 100px;"
                                                                                src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                        </div>
                                                                    </td>
                                                                    <td class="p-3 min-width-200-px">
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
                                                                    <td class="p-3">
                                                                        <div class="inline_block">
                                                                            <input type="number" class="widget__price"
                                                                                name="product[{{ $orderProduct->id }}][price]"
                                                                                value="{{ $orderProduct->product_price }}"
                                                                                hidden>
                                                                            <span>Sku:
                                                                                <strong>{{ $orderProduct->sku }}</strong></span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="p-3" width="120px"
                                                                        style="white-space: nowrap;">
                                                                        <span style="white-space: nowrap;">Số lượng:</span>
                                                                        <span
                                                                            class="check__highlight">{{ $orderProduct->quantity }}</span>
                                                                        sản phẩm
                                                                    </td>
                                                                </tr>
                                                                @php
                                                                    $totalQty += $orderProduct->quantity;
                                                                @endphp
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="5" class="text-end h5">
                                                                    <strong>Tổng số lượng: </strong> <span
                                                                        class="widget__amount">{{ $totalQty }}</span>
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
                                        <form id="myForm" action="{{ route('proposal-agent-receipt.export-file') }}"
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
                                                                                    style="max-width: 100px;"
                                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                    alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                    <td class="p-3 min-width-200-px">
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
                                                                    <td class="p-3">
                                                                        <div class="inline_block">
                                                                            <input type="number" class="widget__price"
                                                                                name="product[{{ $orderProduct->id }}][price]"
                                                                                value="{{ $orderProduct->product_price }}"
                                                                                hidden>
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
                                                                <td colspan="5" class="text-end h5">
                                                                    <strong>Tổng số lượng: </strong> <span
                                                                        class="widget__amount">{{ $totalQty }}</span>
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
                                    <form id="myForm" action="{{ route('agent-receipt.export-file') }}"
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
        </div>


        @if (
            $proposal->status != \Botble\Agent\Enums\ProposalAgentEnum::DENIED &&
                $proposal->status != \Botble\Agent\Enums\ProposalAgentEnum::PENDING)
            <div class="card-footer text-center"
                style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                    data-bs-target="#printProposal" data-bs-whatever="@mdo">
                    {{ __('In phiếu đề xuất') }}
                </button>
            </div>
        @endif
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
