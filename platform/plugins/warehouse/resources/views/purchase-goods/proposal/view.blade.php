@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<style>
    .flexbox-grid-default{
        position: relative;
    }
    .card-notify-year{
        position: absolute;
        right: -15px;
        top: -15px;
        background: #ff4444;
        text-align: center;
        color: #fff;
        font-size: 14px;
        padding: 5px;
        padding-left: 30px;
        clip-path: polygon(
            0% 0%, 100% 0%, 100% 100%, 0% 100%, 10% 50%, 0% 0%
        );
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        z-index: 3;
    }

    .card-notify-year.blue{
        background: rgb(74, 74, 236);
    }

    .tag__product{
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
if(isset($receipt) || empty($receipt)){
    $strCenter =  'justify-content-center';
}
@endphp
    <div class="row row-cards widget__view {{$strCenter}}
    ">
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
                                            class="title-product-main text-no-bold">{{ __('Phiếu đề xuất mua hàng') }} <strong>{{$proposal->code }}</strong>
                                             - Người đề xuất: {{ $proposal->invoice_issuer_name }}</label>
                                             <div>
                                                Ngày tạo: <span>{{ date('d/m/Y', strtotime($proposal->created_at)) }}</span>
                                            </div>
                                            <div>
                                                Kho: <strong>{{$proposal->warehouse_name }}</strong>
                                            </div>
                                            <div>
                                                Tiêu đề: {{$proposal->title }}
                                            </div>
                                        </div>
                                        <span class="card-notify-year red">Phiếu đề xuất</span>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided table-vcenter card-table w-100">
                                            <tbody>
                                            @php
                                            $totalPrice=0;
                                            @endphp
                                                @foreach ($proposal->proposalDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if($orderProduct->material_id !== 0)
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 70px;"
                                                                    src="{{ RvMedia::getImageUrl($orderProduct->material($orderProduct->material_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                    alt="{{ $orderProduct->material($orderProduct->material_id)->first()?->name }}"
                                                                >
                                                            </div>
                                                            @else
                                                            <span class="tag__product">Mới</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-2 min-width-200-px">
                                                            {{$orderProduct->material_name }}
                                                            <br>
                                                            Từ: {{$orderProduct->supplier_name}}
                                                        </td>
                                                        <td class="p-2">
                                                            Mã: <span class="check__highlight">{{!empty($orderProduct->material_code) ? $orderProduct->material_code : '' }}</span>
                                                        </td>
                                                        <td class="p-2 text-end">
                                                            <div class="inline_block">
                                                                <span class="check__highlight">{{ format_price($orderProduct->material_price) }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="p-2 text-center">x</td>
                                                        <td class="p-2">
                                                            <span class="check__highlight">{{ ($orderProduct->material_quantity) }}</span>
                                                        </td>
                                                        <td class="p-2">
                                                            Đơn vị: <span class="check__highlight">{{!empty($orderProduct->material_unit) ? $orderProduct->material_unit : '' }}</span>
                                                        </td>
                                                        <td class="pl5 text-end">
                                                            <span class="check__highlight">{{ format_price($orderProduct->material_price * $orderProduct->material_quantity) }}</span>
                                                        </td>
                                                    </tr>
                                                    @php
                                                    $totalPrice += $orderProduct->price_import * $orderProduct->quantity;
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="8" class="text-end h5">
                                                        <input type="number" name="ac_amount" value="{{$proposal->amount}}" class="ac_amount" hidden>
                                                        <strong>Tổng tiền: </strong> <span class="widget__amount">{{format_price($proposal->total_amount)}}</span>
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
                                                <textarea
                                                    class="form-control textarea-auto-height"
                                                    name="description"
                                                    rows="4"
                                                    placeholder="{{ __('Ghi chú') }}"
                                                    disabled
                                                >{{ $proposal->description }}</textarea>
                                            </div>
                                        </div>
                                        @php
                                            $inputDate = $proposal->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                            $formattedDate = date('d/m/Y', strtotime($inputDate));
                                        @endphp
                                        <div class="col-lg-6 col-sm-12">
                                            <div class="py-3">
                                                <label
                                                    class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                    <input class="form-control flatpickr-input"
                                                    value="{{ $formattedDate }}"
                                                    data-date-format="d-m-Y" v-pre="1"
                                                    placeholder="d-m-Y" data-input=""
                                                    readonly="readonly" name="expected_date"
                                                    type="text" id="expected_date"
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

        @if(Auth::user()->hasPermission('proposal-purchase-goods.receipt'))
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
                                            <label
                                                class="title-product-main text-no-bold">{{ __('Phiếu mua hàng') }} <strong>{{$receipt->proposal->code }}</strong>
                                                - Người xác nhận: {{ $receipt->invoice_issuer_name }}</label>
                                                <div>
                                                    Ngày tạo: <span>{{ date('d/m/Y', strtotime($receipt->created_at)) }}</span>
                                                </div>
                                                <div>
                                                    Kho: <strong>{{$receipt->warehouse_name }}</strong>
                                                </div>
                                                <div>
                                                    Tiêu đề: {{$receipt->title }}
                                                </div>
                                        </div>
                                        <span class="card-notify-year blue">Phiếu đã xác nhận</span>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided table-vcenter card-table w-100">
                                            <tbody>
                                            @php
                                            $totalPriceReceipt=0;
                                            @endphp
                                                @foreach ($receipt->receiptDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if($orderProduct->material_id !== 0)
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 70px;"
                                                                    src="{{ RvMedia::getDefaultImage() }}"
                                                                    alt="{{ $orderProduct->material_name }}"
                                                                >
                                                            </div>
                                                            @else
                                                            <span class="tag__product">Mới</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-2 min-width-200-px">
                                                                {{ $orderProduct->material_name }}
                                                                <br>
                                                            Từ: {{$orderProduct->supplier_name}}
                                                        </td>
                                                        <td class="p-2">
                                                            Mã: <span class="check__highlight">{{$orderProduct->material_code }}</span>
                                                        </td>
                                                        <td class="p-2 text-end">
                                                            <div class="inline_block">
                                                                <span class="check__highlight">{{ format_price($orderProduct->material_price) }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="p-2 text-center">x</td>
                                                        <td class="p-2">
                                                            <span class="check__highlight">{{ $orderProduct->material_quantity }}</span>
                                                        </td>
                                                        <td class="p-2">
                                                            Đơn vị: <span class="check__highlight">{{$orderProduct->material_unit}}</span>
                                                        </td>
                                                        <td class="pl5 text-end">
                                                            <span class="check__highlight">{{ format_price($orderProduct->material_price * $orderProduct->material_quantity) }}</span>
                                                        </td>
                                                    </tr>

                                                    @php
                                                    $totalPriceReceipt += $orderProduct->material_price * $orderProduct->material_quantity;
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="8" class="text-end h5">
                                                        <strong>Tổng tiền: </strong> <span class="widget__amount">{{format_price($receipt->total_amount)}}</span>
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
                                                <textarea
                                                    class="form-control textarea-auto-height"
                                                    name="description"
                                                    rows="4"
                                                    placeholder="{{ __('Ghi chú') }}"
                                                    disabled
                                                >{{ $receipt->description }}</textarea>
                                            </div>
                                        </div>
                                        @php
                                            $inputDate = $receipt->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                            $formattedDate = date('d/m/Y', strtotime($inputDate));
                                        @endphp
                                        <div class="col-lg-6 col-sm-12">
                                            <div class="py-3">
                                                <label
                                                    class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                <input class="form-control flatpickr-input"
                                                value="{{ $formattedDate }}"
                                                data-date-format="d-m-Y" v-pre="1"
                                                placeholder="d-m-Y" data-input=""
                                                readonly="readonly" name="expected_date"
                                                type="text" id="expected_date"
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
        @endisset
        @endif
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const widget = document.querySelectorAll('.widget__view .ui-layout__item');

            if(widget && widget.length === 2)
            {
                const wp_porposal = widget[0];
                const wp_receipt = widget[1];

                const itemHighlightPorposal = wp_porposal.querySelectorAll('.check__highlight');
                const itemHighlightReceipt = wp_receipt.querySelectorAll('.check__highlight');

                for (let index = 0; index < itemHighlightPorposal.length; index++) {
                    if(itemHighlightPorposal[index].textContent !== itemHighlightReceipt[index].textContent)
                    {
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
