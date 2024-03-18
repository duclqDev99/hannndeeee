@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
@php
use Botble\Warehouse\Enums\MaterialReceiptStatusEnum;
@endphp
<style>
    .flexbox-grid-default{
        position: relative;
    }
    .card-notify-year{
        position: absolute;
        right: -10px;
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
    <div class="widget__view row row-cards justify-content-center">
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
                                                class="title-product-main text-no-bold">
                                                @if($receipt->is_purchase_goods === 1)
                                                {{__('Phiếu mua hàng')}} <strong>{{$receipt->proposal->code}}</strong>
                                                @else
                                                {{ __('Phiếu nhập kho') }} <strong>{{$receipt->proposal->proposal_code}}</strong>
                                                @endif 
                                                - Người nhập kho: {{ $receipt->invoice_issuer_name }},</label>
                                                lúc <span>{{ date('d/m/Y', strtotime($receipt->created_at)) }}</span>
                                                <div>
                                                    @if($receipt->is_from_supplier === 0)
                                                    Nhập từ: <strong>{{ $receipt->wh_departure_name }} <i class="fa-solid fa-arrow-right"></i> {{ $receipt->warehouse_name }}</strong>
                                                    @else
                                                    Kho: {{ $receipt->warehouse_name }}
                                                    @endif
                                                </div>
                                                <div>
                                                    Tiêu đề: {{ $receipt->title }} 
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
                                            $totalQuantity=0;
                                            @endphp
                                                @foreach ($receipt->receiptDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if($orderProduct->material_id !== 0)
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                                                    src="{{ RvMedia::getImageUrl($orderProduct->material($orderProduct->material_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                    alt="{{ $orderProduct->material($orderProduct->material_id)->first()?->name }}"
                                                                >
                                                            </div>
                                                            @else
                                                            <span class="tag__product">Mới</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 min-width-200-px">
                                                                {{ $orderProduct->material_name }}
                                                                @if($receipt->is_from_supplier === 1)
                                                                <br>
                                                                Từ: {{$orderProduct->supplier_name}}
                                                                @endif
                                                        </td>
                                                        <td class="p-3">
                                                            Mã: {{$orderProduct->material_code }}
                                                        </td>
                                                        <td class="p-3 text-end">
                                                            Số lượng: <span class="check__highlight">{{ $orderProduct->material_quantity }}</span>
                                                        </td>
                                                    </tr>

                                                    @php 
                                                    $totalQuantity += $orderProduct->material_quantity;
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="6" class="text-end h5">
                                                        <strong>Tổng số lượng: </strong> <span class="widget__amount">{{($totalQuantity)}}</span>
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
                                                    class="text-title-field">Ghi chú: </label>
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
                            @if($receipt->status == MaterialReceiptStatusEnum::APPOROVED)
                            <div class="card-footer text-center" style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@mdo">
                                    {{ __('In phiếu nhập kho') }}
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
                        <h5 class="modal-title" id="exampleModalLabel">{{ trans('plugins/warehouse::bill_export.title_form_in') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="myForm" action="{{route('material-receipt-pdf.export-receipt')}}" method="POST">
                            @csrf
                            <input type="text" class="form-control" id="id" value="{{$receipt->id}}" name="id" hidden="hidden">
                            <input type="text" class="form-control" id="type_bill" value="{{ trans('plugins/warehouse::bill_export.title_form_in') }}" name="type_bill" hidden="hidden">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label required">{{ __('Người lập biểu') }}:</label>
                                <input type="text" class="form-control" id="proposal_name" value="{{$receipt->invoice_issuer_name}}" name="proposal_name">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label required">{{  __('Kế toán hoặc trưởng bộ phận') }}:</label>
                                <input type="text" class="form-control" id="receiver_name" name="receiver_name">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label required">{{ trans('plugins/warehouse::bill_export.storekeeper_name') }}:</label>
                                <input type="text" class="form-control" id="storekeeper_name" name="storekeeper_name" value="{{auth()->user()->last_name . ' ' . auth()->user()->first_name}}">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label required">{{ __('Người giao hàng') }}:</label>
                                <input type="text" class="form-control" id="chief_accountant_name" name="chief_accountant_name">
                            </div>
                            {{-- <div class="mb-3">
                                <label for="recipient-name" class="col-form-label required">{{ trans('plugins/warehouse::bill_export.manager_name') }}:</label>
                                <input type="text" class="form-control" id="manager_name" name="manager_name">
                            </div> --}}
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label required">{{ trans('plugins/warehouse::bill_export.today') }}:</label>
                                <input type="date" class="form-control" id="today" value="{{date('Y-m-d')}}" name="today">
                            </div>
                            <div style="float: right">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" id="print" name="button_type" value="print"> <i class="fas fa-print"></i> {{ trans('plugins/ecommerce::invoice.print') }}</button>
                                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" id="download" name="button_type" value="download"><i class="fas fa-download"></i> {{ trans('plugins/ecommerce::invoice.download') }}</button>
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
                                <div class="pd-all-20">
                                    <div class="card-header flexbox-grid-default">
                                        <div class="flexbox-auto-right mr5">
                                            <label
                                                class="title-product-main text-no-bold">{{ __('Phiếu thực nhập') }} <strong>{{$actual->proposal}}</strong>
                                                 - Người nhập: {{ $actual->invoice_confirm_name }},</label>
                                                 lúc <span>{{ date('d/m/Y', strtotime($actual->created_at)) }}</span>
                                            <div>
                                                Kho: {{ $receipt->warehouse_name }}
                                            </div>
                                            <div>
                                                Tiêu đề: {{ $receipt->title }}
                                            </div>
                                        </div>
                                        <span class="card-notify-year red">Phiếu thực nhập</span>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided table-vcenter card-table w-100">
                                            <tbody>
                                            @php
                                            $totalQuantityActual=0;
                                            @endphp
                                                @foreach ($actual->autualDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if($orderProduct->material_id !== 0)
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                                                    src="{{ RvMedia::getImageUrl($orderProduct->material($orderProduct->material_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                    alt="{{ $orderProduct->material($orderProduct->material_id)->first()?->name }}"
                                                                >
                                                            </div>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 min-width-200-px">
                                                                {{ $orderProduct->material_name }}
                                                        </td>
                                                        <td class="p-3">
                                                            Mã: {{$orderProduct->material_code }}
                                                        </td>
                                                        <td class="p-3 text-end">
                                                            Số lượng: <span class="check__highlight">{{ $orderProduct->material_quantity }}</span>
                                                        </td>
                                                    </tr>

                                                    @if(!empty($orderProduct->reasoon))
                                                    <tr class="">
                                                        <td><strong>Lý do:</strong> </td>
                                                        <td colspan="3">{{$orderProduct->reasoon}}</td>
                                                    </tr>
                                                    @endif
                                                    @php 
                                                    $totalQuantityActual += $orderProduct->material_quantity;
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="6" class="text-end h5">
                                                        <strong>Tổng số lượng: </strong> <span class="widget__total_quantity">{{($totalQuantityActual)}}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-body pd-all-20 p-none-t">
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
