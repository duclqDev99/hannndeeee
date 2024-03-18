@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\Warehouse\Models\QuantityMaterialStock;
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

        /* General Styles */
    </style>

    @include('plugins/warehouse::material/receipt/modals/reciept-out-scan')
    @include('plugins/warehouse::material/receipt/modals/reciept-out-scan-pc')

    {{-- new --}}
    <div>
        <div class="d-flex justify-content-end gap-3 mb-3">
            <button type="button" id="open_scan_modal_pc"
                class=" btn btn-primary d-none gap-2 align-items-center justify-items-center d-md-flex">
                <i class="fa-solid fa-qrcode"></i>
                <span class="d-md-inline">Quét QR</span>
            </button>
            <button type="button" id="open_scan_modal"
                class=" btn btn-primary d-flex gap-2 align-items-center justify-items-center d-md-none">
                <i class="fa-solid fa-qrcode"></i>
                <span class="d-md-inline">Quét QR</span>
            </button>
        </div>
        <div class="widget__view row row-cards justify-content-center">
            <div class="col-lg-6 col-md-12">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="card-header flexbox-grid-default">
                                            <div class="flexbox-auto-right mr5">
                                                <label class="h4 title-product-main text-no-bold">{{ __('Phiếu xuất kho') }}
                                                    <strong>{{ $goodIssue->proposal->proposal_code }}</strong> -
                                                    Xuất từ: <strong>{{ $goodIssue->warehouse_name }}
                                                        <i class="fa-solid fa-arrow-right"></i>
                                                        {{ $goodIssue->proposal->warehouse->name }}</strong>
                                                    lúc
                                                    <span>{{ date('d/m/Y', strtotime($goodIssue->created_at)) }}</span>
                                                </label>
                                                <div>
                                                    Mã đơn hàng: <strong>{{ $goodIssue->general_order_code }}</strong>
                                                </div>
                                                <div>
                                                    Người đề xuất: <strong>{{ $goodIssue->invoice_issuer_name }}</strong>
                                                </div>
                                                <div>
                                                    Tiêu đề: {{ $goodIssue->title }}
                                                </div>
                                                <button class="btn btn-light d-flex gap-2 align-items-center d-md-none"
                                                    data-bs-toggle="collapse" href="#form1" role="button"
                                                    aria-expanded="true" ria-controls="form1">
                                                    <span class="d-none d-md-inline">Xem phiếu xuất</span>
                                                    <i class="fa-solid fa-chevron-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="table-wrap">
                                            <table class="table-order table-divided table-vcenter card-table w-100">
                                                <tbody>
                                                    @php
                                                        $totalQty = 0;
                                                    @endphp
                                                    @foreach ($goodIssue->proposalOutDetail as $issueDetail)
                                                        <tr class="item__product">
                                                            <td class="width-60-px min-width-60-px vertical-align-t">
                                                                <div class="wrap-img">
                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                        width="100px"
                                                                        src="{{ RvMedia::getImageUrl($issueDetail->material($issueDetail->material_code)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $issueDetail->material($issueDetail->material_code)->first()?->name }}">
                                                                </div>

                                                            </td>
                                                            <td class="p-3 min-width-200-px">
                                                                <div class="inline_block">

                                                                    <span>Tên: <strong>
                                                                            {{ $issueDetail->material_name }}
                                                                        </strong>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td class="p-3 min-width-200-px">


                                                                <span>Mã:
                                                                    <strong>{{ $issueDetail->material_code }}</strong></span>

                                                                <input type="number" class="widget__price"
                                                                    name="material[56][material_price]" value="2500"
                                                                    hidden="">
                                                            </td>
                                                            <td class="p-3 min-width-200-px text-end">
                                                                <span>Số lượng yêu cầu:
                                                                    {{ $issueDetail->material_quantity }}</span>
                                                            </td>
                                                        </tr>

                                                        @php
                                                            $totalQty += $issueDetail->material_quantity;
                                                        @endphp
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="4" class="text-end h5">
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
                                                    <label class="text-title-field">Ghi chú: </label>
                                                    <textarea class="form-control textarea-auto-height" name="description" rows="4" placeholder="{{ __('Ghi chú') }}"
                                                        disabled>{{ $goodIssue->description }}</textarea>
                                                </div>
                                            </div>
                                            @php
                                                $inputDate = $goodIssue->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
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

            <div class="col-lg-6 col-md-12">
                <form method="POST" action="{{ route('goods-issue-receipt.confirm', $goodIssue->id) }}">
                    <input type="hidden" name="good_issue_id" value="{{ $goodIssue->id }}" />

                    <div class="h-md-100 card border-0">
                        <br>
                        <br>
                        <div class="card-header p-3 bg-white  position-relative">

                            <div class="d-flex justify-content-between">
                                <div class="w-75">
                                    <input type="text" name="warehouse_id" id="" hidden
                                        value="{{ $goodIssue->warehouse_id }}">
                                    <h5 class="card-title fw-bold mb-0">Điều chỉnh phiếu xuất kho
                                        {{ $goodIssue->warehouse_name }}
                                    </h5>
                                </div>

                            </div>
                            {{-- <p class="card-text">Phiếu xuất kho</p> --}}
                        </div>


                        <div class="card-body p-3 bg-white">
                            @csrf
                            <div id="table-wrapper">
                                <table id="table-content" class="table-order table-divided table-vcenter card-table">

                                    <tbody>
                                        @php
                                            $totalQty = 0;
                                            $materialBatches = [];
                                        @endphp
                                        @foreach ($goodIssue->proposalOutDetail as $issueMaterial)
                                            @php
                                                $totalQuantity = $issueMaterial->material_quantity;
                                                $totalQty += $totalQuantity;
                                                $quantityStock = QuantityMaterialStock::where(['material_id' => $issueMaterial->material($issueMaterial->material_code)->first()?->id, 'warehouse_id' => $goodIssue->warehouse_id])->first()->quantity;
                                            @endphp
                                            <tr class="item__product">
                                                <input type="hidden" id="material_id"
                                                    name="material[{{ $issueMaterial->id }}][material_id]"
                                                    value="{{ $issueMaterial->material($issueMaterial->material_code)->first()?->id }}" />
                                                <input type="hidden" id="warehouse_id"
                                                    value="{{ $goodIssue->warehouse_id }}" />
                                                <input type="hidden" id="issueMaterialId"
                                                    value="{{ $issueMaterial->id }}" />
                                                <td class="width-60-px min-width-60-px vertical-align-t">
                                                    <div class="wrap-img">
                                                        <img class="thumb-image thumb-image-cartorderlist" width="100px"
                                                            src="{{ RvMedia::getImageUrl($issueMaterial->material($issueMaterial->material_code)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                            alt="{{ $issueMaterial->material($issueMaterial->material_code)->first()?->name }}">
                                                    </div>
                                                </td>
                                                <td class="p-3 min-width-200-px">
                                                    {{ $issueMaterial->material_name }}
                                                    <input type="hidden"
                                                        name="material[{{ $issueMaterial->id }}][material_code]"
                                                        value="{{ $issueMaterial->material_code }}">
                                                    <br> <span>Mã:
                                                        <strong>{{ $issueMaterial->material_code }}</strong></span>
                                                </td>
                                                <td class="p-3 text-center" width="200px">
                                                    <div class="d-flex text-center">
                                                        <input type="hidden" name="quantityStock" id="quantityStock"
                                                            value="{{ $quantityStock }}" />
                                                        Tồn kho: <span> {{ $quantityStock }}</span>
                                                    </div>
                                                </td>
                                                <td class="p-3 text-end" width="200px">
                                                    <div class="d-flex align-items-center">
                                                        <span style="white-space: nowrap;">Số lượng: </span>
                                                        <input type="number" data-name="quantity"
                                                            class="form-control text-center border-0"
                                                            name="material[{{ $issueMaterial->id }}][quantity]"
                                                            value="{{ $totalQuantity }}"
                                                            default-value="{{ $totalQuantity }}" min="0"
                                                            max="{{ $quantityStock }}" placeholder="0"
                                                            data-batch-id="{{ $issueMaterial->id }}">
                                                    </div>
                                                </td>
                                            <tr>
                                                <td colspan="4" style="padding: 0;">
                                                    <div style="width: 50%; float: right;">
                                                        <table class="table table-bordered" id="table-add"
                                                            style="width: 100%;">

                                                            @foreach ($issueMaterial->materialBatch as $batch)
                                                                <tr>
                                                                    <td>{{ $batch->batch_code }}</td>
                                                                    <td>
                                                                        @if ($totalQuantity - $batch->quantity > 0)
                                                                            <input type="hidden" data-name="batchCode"
                                                                                data-code="{{ $batch->batch_code }}"
                                                                                name="materialDetai[{{ $batch->id }}][quantity_actual]"
                                                                                value="{{ $batch->quantity }}">
                                                                            <input type="hidden"
                                                                                name="materialDetai[{{ $batch->id }}][issueMaterial]"
                                                                                value="{{ $issueMaterial->id }}">
                                                                            <span>Số lượng: {{ $batch->quantity }} </span>
                                                                            @php
                                                                                $totalQuantity -= $batch->quantity;
                                                                                $item = $batch;
                                                                                $item['material_name'] = $issueMaterial->material_name;
                                                                                $item['quantity'] = (int) $totalQuantity;
                                                                                $materialBatches[] = $item;
                                                                            @endphp
                                                                        @else
                                                                            <input type="hidden"
                                                                                name="materialDetai[{{ $batch->id }}][quantity_actual]"
                                                                                value="{{ $totalQuantity }}">
                                                                            <input type="hidden"
                                                                                name="materialDetai[{{ $batch->id }}][issueMaterial]"
                                                                                value="{{ $issueMaterial->id }}">
                                                                            <span>Số lượng: {{ $totalQuantity }}</span>
                                                                            @php
                                                                                $item = $batch;
                                                                                $item['material_name'] = $issueMaterial->material_name;
                                                                                $item['quantity'] = (int) $totalQuantity;
                                                                                $materialBatches[] = $item;
                                                                            @endphp
                                                                        @break
                                                                    @endif

                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                        </tr>
                                        <tr class="collapse" id="collapse{{ $issueMaterial->id }}"
                                            data-batch-id="{{ $issueMaterial->id }}">
                                            <td>
                                                <label>Lý do: </label>
                                            </td>
                                            <td colspan="3">
                                                <textarea class="form-control" name="material[{{ $issueMaterial->id }}][reason]" rows="1"
                                                    placeholder="Vui lòng ghi rõ lý do"></textarea>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @push('footer')
                                        <script>
                                            window.materialBatches = {!! json_encode($materialBatches) !!};
                                        </script>
                                    @endpush

                                    <tr>
                                        <td colspan="4" class="text-end h5">
                                            <strong>Tổng số lượng: </strong> <span
                                                class="widget__amount">{{ $totalQty }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button class="btn btn-primary">Xác nhận</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@stop

@push('header')
<style>
    .tag__product {
        background: rgb(233, 99, 99);
        color: #fff;
        padding: 5px 10px;
        border-radius: 99px;
        font-size: .85em;
        text-align: center;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    }

    .card-notify-year.blue {
        background: rgb(74, 74, 236);
    }

    .card-notify-year {
        position: absolute;

        inset: 0;
        left: 80%;
        background: #ff4444;
        text-align: center;
        color: #fff;
        font-size: 14px;
        padding: 5px;
        padding-left: 30px;
        clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 10% 50%, 0% 0%);
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }

    /* add */
    input[type='number']::-webkit-inner-spin-button,
    input[type='number']::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .quantity_reduce_btn,
    .quantity_increment_btn,
    input[type='number'] {
        border-radius: 0% !important;
        max-height: 1.7rem !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .checkmark {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: block;
        stroke-width: 5;
        stroke: #36c6d3;
        stroke-miterlimit: 10;
        box-shadow: inset 0px 0px 0px #36c6d3;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        margin: 0 auto;
    }

    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 5;
        stroke-miterlimit: 10;
        stroke: #36c6d3;
        fill: #fff;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes scale {

        0%,
        100% {
            transform: none;
        }

        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }

    @keyframes fill {
        100% {
            box-shadow: inset 0px 0px 0px 30px #36c6d3;
        }
    }
</style>
@endpush
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

@push('header')
<meta name="apple-mobile-web-app-capable" content="yes">
@endpush

{{-- @push('footer')
    <script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
@endpush --}}
