@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<style>
    .flexbox-grid-default{
        position: relative;
    }
    .card-notify-year{
        position: absolute;
        right: -50px;
        top: -20px;
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
        <div class="col-lg-9 col-md-12">
            <div class="card ui-layout">
                <div class="flexbox-layout-sections">
                    <div class="flexbox-layout-section-primary mt20">
                        <div class="ui-layout__item">
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="card-header flexbox-grid-default">
                                        <div class="flexbox-auto-right mr5">
                                            <label
                                                class="title-product-main text-no-bold">{{ __('Phiếu mua hàng') }} <strong>{{$receipt->proposal->code }}</strong>
                                                 - Người duyệt: <strong>{{$receipt->invoice_issuer_name }}</strong></label>
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
                                            $totalQuantity=0;
                                            @endphp
                                                @foreach ($receipt->receiptDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if($orderProduct->material_id !== 0)
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                                                    src="{{ RvMedia::getDefaultImage() }}"
                                                                    alt="{{ $orderProduct->material_name }}"
                                                                >
                                                            </div>
                                                            @else
                                                            <span class="tag__product">Mới</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 min-width-200-px">
                                                                {{ $orderProduct->material_name }}
                                                                <br>{{$orderProduct->supplier_name }}
                                                        </td>
                                                        <td class="p-3">
                                                            Mã: {{$orderProduct->material_code }}
                                                        </td>
                                                        <td class="p-3">
                                                            <span class="check__highlight">{{ format_price($orderProduct->material_price) }}</span>
                                                        </td>
                                                        <td class="p-3 text-start">x</td>
                                                        <td class="p-3 text-center">
                                                            <span class="check__highlight">Số lượng: {{ $orderProduct->material_quantity }}</span>
                                                        </td>
                                                        <td class="text-start">
                                                            <span>{{ $orderProduct->material_unit }}</span>
                                                        </td>
                                                        <td class="p-3 text-end">
                                                            <strong>{{ format_price($orderProduct->material_price * $orderProduct->material_quantity) }}</strong>
                                                        </td>
                                                    </tr>
                                                    @php $totalQuantity+= $orderProduct->material_quantity
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="8" class="text-end h4">
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