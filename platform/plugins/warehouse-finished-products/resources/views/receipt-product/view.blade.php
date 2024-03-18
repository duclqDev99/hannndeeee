@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php 
use \Botble\WarehouseFinishedProducts\Models\ActualReceiptQrcode;
@endphp
@section('content')
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

    #accordion .card-body{
        cursor: pointer;
    }

    .group-batch-detail{
        padding: 20px;
    }
</style>
@php
$strCenter = '';
if(isset($receipt) || empty($receipt)){
    $strCenter =  'justify-content-center';
}
@endphp
    <div class="widget__view row row-cards {{$strCenter}}
    ">
        @isset($proposal)
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
                                            class="title-product-main text-no-bold">{{ __('Phiếu đề xuất nhập kho') }}
                                            <strong>{{ get_proposal_receipt_product_code($proposal->proposal_code) }}</strong> - Người đề xuất: {{ $proposal->invoice_issuer_name }}</label>
                                            <div>
                                                Ngày tạo: {{ date('d/m/Y', strtotime($proposal->created_at)) }}
                                            </div>
                                            <div>
                                                Kho: <strong>{{ $proposal->warehouse_name }}</strong>
                                                - Loại nhập kho:
                                                @if ($proposal->is_warehouse != 'inventory')
                                                    Nhập từ kho <strong>{{$proposal->wh_departure_name}}</strong>
                                                @else
                                                    Nhập kho hàng tồn
                                                @endif
                                            </div>
                                            <div>
                                                Mục đích: {{$proposal->title}}
                                            </div>
                                            @if($proposal->status == \Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum::DENIED)
                                                <div>
                                                    Lý do từ chối: {{$proposal->reasoon_cancel}}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="card-notify-year red">Phiếu đề xuất</span>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided table-vcenter card-table w-100">
                                            <tbody>
                                                @php
                                                $totalQty = 0;
                                                @endphp
                                                @foreach ($proposal->proposalDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if($orderProduct->product_id !== 0)
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                    alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}"
                                                                >
                                                            </div>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 min-width-200-px">
                                                            {{$orderProduct->product_name }}
                                                            @if($proposal->is_warehouse == 'inventory')
                                                            , màu: {{$orderProduct->color}}, size: {{$orderProduct->size}}
                                                            @endif
                                                            @if($proposal->is_warehouse === 0)
                                                            <br>
                                                            Từ: {{ $orderProduct->processing_house_name }}
                                                            @endif
                                                        </td>
                                                        <td class="p-3">
                                                            <div class="inline_block">
                                                                <input type="number" class="widget__price" name="product[{{ $orderProduct->id }}][price]" value="{{$orderProduct->product_price}}" hidden>
                                                                <span>Sku: <strong>{{ ($orderProduct->sku) }}</strong></span>
                                                            </div>
                                                        </td>
                                                        <td class="p-3" width="120px" style="white-space: nowrap;">
                                                            <span style="white-space: nowrap;">Số lượng:</span>
                                                            <span class="check__highlight">{{ ($orderProduct->quantity) }}{{$proposal->is_warehouse == 'warehouse' ? ' lô' : ''}}</span>
                                                        </td>
                                                    </tr>
                                                    @php
                                                    $totalQty += $orderProduct->quantity;
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="5" class="text-end h5">
                                                        <strong>Tổng số lượng: </strong> <span class="widget__amount">{{($totalQty)}}</span>
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
                                                    class="text-title-field">Ghi chú</label>
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
                                        <div class="col-lg-6 col-md-12">
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
                            @if($proposal->status == \Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum::APPOROVED || $proposal->status == \Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum::CONFIRM || $proposal->status == \Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum::WAIT)
                            <div class="card-footer text-center" style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#printProposal" data-bs-whatever="@mdo">
                                    {{ __('In phiếu đề xuất') }}
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="printProposal" tabindex="-1" aria-labelledby="printProposal" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{ trans('plugins/warehouse::bill_export.title_form_in') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="myForm" action="{{route('proposal-receipt-products.export-file')}}" method="POST">
                                @csrf
                                <input type="text" class="form-control" id="id" value="{{$proposal->id}}" name="id" hidden="hidden">
                                <input type="text" class="form-control" id="type_bill" value="{{ trans('plugins/warehouse::bill_export.title_form_in') }}" name="type_bill" hidden="hidden">
                                <div class="mb-3">
                                    <label for="recipient-name" class="col-form-label required">{{ __('Người lập biểu') }}:</label>
                                    <input type="text" class="form-control" id="proposal_name" value="{{$proposal->invoice_issuer_name}}" name="proposal_name">
                                </div>
                                <div class="mb-3">
                                    <label for="recipient-name" class="col-form-label required">{{  __('Kế toán hoặc trưởng bộ phận') }}:</label>
                                    <input type="text" class="form-control" id="receiver_name" name="receiver_name" required>
                                </div>
                                <div style="float: right">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" id="print" name="button_type" value="print"> <i class="fas fa-print"></i> {{ __('In phiếu đề xuất') }}</button>
                                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" id="download" name="button_type" value="download"><i class="fas fa-download"></i> {{ __('Tải phiếu đề xuất') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endisset

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
                                            <input type="text" id="type_check_highlight" value="{{$receipt->is_warehouse}}" hidden>
                                            <label
                                                class="title-product-main text-no-bold">{{ __('Phiếu nhập kho') }}
                                                <strong>{{ get_proposal_receipt_product_code($receipt->receipt_code) }}</strong> - Người tạo: {{ $receipt->invoice_issuer_name }}</label>
                                                <div>
                                                    Ngày tạo: {{ date('d/m/Y', strtotime($receipt->created_at)) }}
                                                </div>
                                                <div>
                                                    @if($receipt->is_warehouse == 'warehouse')
                                                    Nhập từ: <strong>{{ $receipt->wh_departure_name }} <i class="fa-solid fa-arrow-right"></i> {{ $receipt->warehouse_name }}</strong>
                                                    @else
                                                    Kho: {{ $receipt->warehouse_name }}
                                                    @endif
                                                    - Loại nhập kho:
                                                    @if ($receipt->is_warehouse == 'warehouse')
                                                        Nhập lô từ kho {{$receipt->wh_departure_name}}
                                                    @elseif ($receipt->is_warehouse == 'warehouse-odd')
                                                        Nhập lẻ từ kho {{$receipt->wh_departure_name}}
                                                    @else
                                                        Nhập kho hàng tồn
                                                    @endif
                                                </div>
                                                <div>
                                                    Mục đích: {{$receipt->title}}
                                                </div>
                                                @if($receipt->status == \Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum::CANCEL)
                                                    <div>
                                                        Lý do từ chối: {{$receipt->reasoon}}
                                                    </div>
                                                @endif
                                        </div>
                                        <span class="card-notify-year blue">Phiếu nhập kho</span>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided table-vcenter card-table w-100">
                                            <tbody>
                                            @php
                                            $totalQty=0;
                                            @endphp
                                                @foreach ($receipt->receiptDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if($orderProduct->product_id !== 0)
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                    alt="{{ $orderProduct->product->name }}"
                                                                >
                                                            </div>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 min-width-200-px">
                                                            {{ $orderProduct->product_name }}
                                                        @if($receipt->is_warehouse != 'warehouse')
                                                            , màu: {{$orderProduct->color}}, size: {{$orderProduct->size}}
                                                            @endif
                                                        </td>
                                                        <td class="p-3">
                                                            <div class="inline_block">
                                                                <input type="number" class="widget__price" name="product[{{ $orderProduct->id }}][price]" value="{{$orderProduct->price}}" hidden>
                                                                <span>Sku: <strong>{{ ($orderProduct->sku) }}</strong></span>
                                                            </div>
                                                        </td>
                                                        <td class="p-3">
                                                            Số lượng: <span class="check__highlight">{{ $orderProduct->quantity }}</span> {{$receipt->is_warehouse == 'warehouse' ? 'lô' : ''}}
                                                        </td>
                                                    </tr>

                                                    @php
                                                    $totalQty += $orderProduct->quantity;
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="5" class="text-end h5">
                                                        <strong>Tổng số lượng: </strong> <span class="widget__amount">{{($totalQty)}}</span>
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
                                        <div class="col-lg-6 col-md-12">
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
                            @if($receipt->status == \Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum::APPOROVED && isset($actual))
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
                        <form id="myForm" action="{{route('receipt-product.export-file')}}" method="POST">
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
                                <label for="recipient-name" class="col-form-label required">{{ trans('Người thủ kho') }}:</label>
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
                                <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" id="print" name="button_type" value="print"> <i class="fas fa-print"></i> {{ __('In phiếu nhập kho') }}</button>
                                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" id="download" name="button_type" value="download"><i class="fas fa-download"></i> {{ __('Tải phiếu nhập kho') }}</button>
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
                                                class="title-product-main text-no-bold">{{ __('Phiếu thực nhập') }} <strong>{{get_proposal_receipt_product_code($actual->receipt->receipt_code)}}</strong>
                                                - Người nhập: {{ $actual->invoice_confirm_name }}</label>
                                            <div>
                                                Ngày nhập: {{ date('d/m/Y', strtotime($actual->created_at)) }}
                                            </div>
                                            <div>
                                                Kho: {{ $receipt->warehouse_name }}
                                            </div>
                                            <div>
                                                Mục đích: {{ $receipt->title }}
                                            </div>
                                        </div>
                                        <span class="card-notify-year red">Phiếu thực nhập</span>
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
                                        @php
                                            $totalActual = 0;
                                            $actualOrderBy = $actual->actualDetail()->orderBy('product_id', 'asc')->get();
                                        @endphp
                                        @foreach($actual->actualDetail as $key => $detailActual)
                                                <tr class="item__product">
                                                    <td class="width-60-px min-width-60-px vertical-align-t">
                                                        @if($detailActual->product_id !== 0)
                                                        <div class="wrap-img">
                                                            <img
                                                                class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                                                src="{{ RvMedia::getImageUrl($detailActual->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                alt="{{ $detailActual->product->name }}"
                                                            >
                                                        </div>
                                                        @endif
                                                    </td>
                                                    <td class="p-3 min-width-200-px">
                                                        {{ $detailActual->product_name }}, {{ $detailActual->product->variationProductAttributes[0]->title}}, {{ $detailActual->product->variationProductAttributes[1]->title}}
                                                    </td>
                                                    <td class="p-3">
                                                        <div class="inline_block">
                                                            <input type="number" class="widget__price" name="product[{{ $detailActual->id }}][price]" value="{{$detailActual->price}}" hidden>
                                                            <span>Sku: <strong>{{ ($detailActual->sku) }}</strong></span>
                                                        </div>
                                                    </td>
                                                    <td class="p-3">
                                                        Số lượng: <span class="check__highlight">{{ $detailActual->quantity }}</span>
                                                    </td>
                                                    <td class="p-3">
                                                        <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapse-product-{{$detailActual->product_id}}" role="button" aria-expanded="false" aria-controls="collapse-product-{{$detailActual->product_id}}">
                                                            <i class="fa-regular fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="collapse" id="collapse-product-{{$detailActual->product_id}}">
                                                            <div class="card card-body">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>Số lượng sản phẩm nhập lẻ: 
                                                                        <strong>
                                                                            @php 
                                                                                $quantityOdd = ActualReceiptQrcode::where([
                                                                                    'receipt_id' => $receipt->id,
                                                                                    'product_id' => $detailActual->product_id,
                                                                                    'is_batch' => 0
                                                                                ])->get()->count();
                                                                                echo $quantityOdd;
                                                                            @endphp
                                                                        </strong>
                                                                    </div>
                                                                    <div>Số lượng sản phẩm được tạo lô: 
                                                                        <strong>
                                                                            @php 
                                                                                $quantityBatch = ActualReceiptQrcode::where([
                                                                                    'receipt_id' => $receipt->id,
                                                                                    'product_id' => $detailActual->product_id,
                                                                                    'is_batch' => 1
                                                                                ])->get()->count();
                                                                                echo $quantityBatch;
                                                                            @endphp
                                                                        </strong>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>

                                                @php
                                                $totalActual += $detailActual->quantity;
                                                @endphp
                                            @endforeach
                                            <tr>
                                                <td colspan="5" class="text-end h5 py-3">
                                                    <strong>Tổng số lượng: </strong> <span class="widget__amount">{{($totalActual)}}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="card meta-boxes mb-3" id="gallery_wrap">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                Hình ảnh đính kèm
                                            </h4>
                                        </div>
                                        @if(!empty($actual->image))
                                        <div class="card-body">
                                            <input id="gallery-data" class="form-control" name="gallery"
                                                type="hidden" value="[]">
                                            <div>
                                                <div class="list-photos-gallery">
                                                    <div class="row g-2" id="list-photos-items">
                                                        @foreach(json_decode($actual->image) as $key => $image)
                                                            <div class="col-md-2 col-sm-3 col-4 photo-gallery-item" data-id="{{$key}}" data-img="{{ RvMedia::getImageUrl($image->img, 'thumb', false, RvMedia::getDefaultImage()) }}" data-description="{{$image->description}}">
                                                                <div class="gallery_image_wrapper">
                                                                    <img src="{{ RvMedia::getImageUrl($image->img, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="image" loading="lazy">
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade modal-blur" id="edit-gallery-item" tabindex="-1"
                                                data-select2-dropdown-parent="true" style="display: none;"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable "
                                                    role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Mô tả của hình ảnh</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <input type="text" class="form-control"
                                                                id="gallery-item-description"
                                                                placeholder="Mô tả...">
                                                        </div>

                                                        <div class="modal-footer">
                                                            <div class="btn-list">
                                                                <button class="btn" type="button"
                                                                    data-bs-dismiss="modal">Thoát</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body pd-all-20 p-none-t">
                                    <button type="button" class="btn btn-primary print-qr-button"
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="Print QR Code"
                                        onclick="printQRCode()"
                                        data-url = {{route('receipt-product.print-qr-code-all', $receipt->id)}}
                                    >In tất cả QR code</button>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main" id="accordion">
                                    @foreach($batchs as $key => $actualBatch)
                                    @php 
                                        $batch = $actualBatch->batch;
                                    @endphp
                                        <div class="col">
                                            <div class="card-body" id="batch-{{$batch->id}}"
                                                data-toggle="collapse" data-target="#collapse-{{$batch->id}}" aria-expanded="true" aria-controls="collapse-{{$batch->id}}"
                                                >
                                                <div class="title"><strong>Lô hàng ({{$key+1}})</strong> - Mã lô: {{$batch->batch_code}}</div>
                                                <div class="body d-flex justify-content-between">
                                                    <div class="start_count">
                                                        Số lượng ban đầu: {{$batch->start_qty}}
                                                    </div>
                                                    <div class="count">
                                                        Số lượng hàng trong lô: {{$batch->productInBatch->count()}}
                                                    </div>
                                                    <div class="created_at">
                                                        Ngày tạo: {{ date('d/m/Y', strtotime($batch->created_at)) }}
                                                    </div>
                                                    <div class="btn-export-qr">
                                                        <button
                                                            type="button"
                                                            data-bs-original-title="Xem chi tiết"
                                                            data-bs-toggle="modal" data-bs-target="#view-detail-batch-{{$batch->id}}" data-bs-whatever="@mdo"
                                                            class="btn btn-sm btn-icon btn-secondary"
                                                            style="background-color: #6f8c91c5"
                                                        >
                                                        <i class="fa-regular fa-eye"></i><span class="sr-only">Xem chi tiết</span>
                                                        </button>
                                                        <button
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="Print QR Code"
                                                            onclick="printQRCode()"
                                                            class="btn btn-sm btn-icon btn-secondary print-qr-button"
                                                            style="background-color: #8ACDD7"
                                                            data-url = {{route('receipt-product.print-qr-code', $batch->id)}}
                                                        >
                                                                <i class="fa fa-qrcode"></i><span class="sr-only">Print QR Code</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @php
                                            $batchGroup = $batch->productInBatch->groupBy('product_id');
                                            @endphp
                                            <div class="group-batch-detail card collapse"
                                            id="collapse-{{$batch->id}}" aria-labelledby="batch-{{$batch->id}}" data-parent="#accordion"
                                            >
                                                @foreach($batchGroup as $batchDetail)
                                                    <div class="item d-flex justify-content-between">
                                                        <div class="name">
                                                            Tên: {{ $batchDetail->first()->product_name}}, {{ $batchDetail->first()->product->variationProductAttributes[0]->title}}, {{ $batchDetail->first()->product->variationProductAttributes[1]->title}}
                                                        </div>
                                                        <div class="sku">
                                                            SKU: {{ $batchDetail->first()->sku}}
                                                        </div>
                                                        <div class="quantity">
                                                            Số lượng: {{ $batchDetail->count()}}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endisset
    </div>
    @if(!empty($batchs))
        @foreach($batchs as $key => $actualBatch)
        @php 
            $batch = $actualBatch->batch;
        @endphp
        <div class="modal fade" id="view-detail-batch-{{$batch->id}}" tabindex="-1" aria-labelledby="view-detail-batch" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="view-detail-batch">Chi tiết lô hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @foreach($batch->productInBatch as $product)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="wrap-img">
                                @php
                                    $logoPath = 'images/logo-handee.png';
                                    $qrCodeWithLogo = QrCode::size(100)
                                        ->format('png')
                                        ->merge($logoPath, 0.3, true)
                                        ->errorCorrection('H')
                                        ->generate($product->statusQrCode->qr_code);
                                @endphp
                                <img class="thumb-image thumb-image-cartorderlist" style="width: 100px; object-fit: contain;" src="data:image/png;base64, {!! base64_encode($qrCodeWithLogo) !!} " alt="{{ $product->product_name }}">
                                {{-- <img
                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                    src="data:image/png;base64, {{ $product->statusQrCode->qr_code }}"
                                    alt="{{ $product->product_name }}"
                                > --}}
                            </div>
                            <h4 class="my-0">{{$product->product_name}}, {{$product->product->variationProductAttributes->first()->title}}, {{$product->product->variationProductAttributes->last()->title}}</h4>
                            <div>Sku: {{$product->sku}}</div>
                        </div>
                        @endforeach
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
        @endforeach
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const widget = document.querySelectorAll('.widget__view .ui-layout__item');

            const typeCheckHighlight = document.querySelector('#type_check_highlight');

            if(typeCheckHighlight && typeCheckHighlight.value != 'warehouse'){
                if(widget && widget.length === 2)
                {
                    const wp_porposal = widget[0];
                    const wp_receipt = widget[1];

                    const itemHighlightPorposal = wp_porposal.querySelectorAll('.check__highlight');
                    const itemHighlightReceipt = wp_receipt.querySelectorAll('.check__highlight');

                    for (let index = 0; index < itemHighlightPorposal.length; index++) {
                        console.log('text contetn: ', itemHighlightPorposal[index].textContent, itemHighlightReceipt[index].textContent);
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
            }
        })
    </script>
@stop
