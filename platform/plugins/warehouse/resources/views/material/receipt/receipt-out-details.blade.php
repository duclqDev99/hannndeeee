@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<style>
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
    <div class="widget__view row justify-content-center">
        <div class="col-lg-6 col-md-12">
            <div class="ui-layout">
                <div class="flexbox-layout-sections">
                    <div class="flexbox-layout-section-primary mt20">
                        <div class="ui-layout__item">
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="flexbox-grid-default">
                                        <div class="flexbox-auto-right mr5">
                                            <label class="h4 title-product-main text-no-bold">{{ __('Phiếu xuất kho') }}
                                                {{ $receipt->warehouse_name }}
                                            </label>
                                            <div>
                                                Mã phiếu mua hàng: <strong>{{$receipt->general_order_code }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided">
                                            <tbody>
                                                @php
                                                $totalQty = 0;
                                                @endphp
                                                @foreach ($receipt->receiptDetail as $orderMaterial)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if($orderMaterial->material_id !== 0)
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist"
                                                                    src="{{ RvMedia::getImageUrl($orderMaterial->material($orderMaterial->material_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                    alt="{{ $orderMaterial->material($orderMaterial->material_id)->first()?->name }}"
                                                                >
                                                            </div>
                                                            @else
                                                            <span class="tag__product">Mới</span>
                                                            @endif
                                                        </td>
                                                        <td class="pl5 p-r5 min-width-100-px">
                                                            {{$orderMaterial->material_name }}
                                                        </td>

                                                        <td class="pl5 p-r5">
                                                            <div class="inline_block">
                                                                <input type="number" class="widget__price" name="material[{{ $orderMaterial->id }}][material_price]" value="{{$orderMaterial->material_price}}" hidden>
                                                                <span>Mã nguyên liệu: <strong>{{ ($orderMaterial->material_code) }}</strong></span>
                                                            </div>
                                                        </td>
                                                        <td class="pl5 p-r5">
                                                            <div class="inline_block">
                                                                <input type="number" class="widget__batch" name="material[{{ $orderMaterial->id }}][material_price]" value="{{$orderMaterial->batch_code}}" hidden>
                                                                <span>Mã lô: <strong>{{ ($orderMaterial->batch_code) }}</strong></span>
                                                            </div>
                                                        </td>
                                                        <td class="pl5 p-r5 text-end" width="120px">
                                                            <input type="number" name="material[{{$orderMaterial->id }}][quantity]" class="form-control widget__quantity" value="{{ $orderMaterial->material_quantity }}" min="0" placeholder="0" hidden>
                                                            <span>Số lượng: {{ $orderMaterial->quantity }}</span>
                                                        </td>
                                                    </tr>
                                                    @php
                                                    $totalQty += $orderMaterial->quantity;
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="4" class="text-end h5">
                                                        <strong>Tổng số lượng: </strong> <span class="widget__amount">{{($totalQty)}}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t">
                                    <div class="flexbox-grid-default">
                                        <div class="flexbox-auto-right p-r5 d-sm-flex">
                                            <div class="py-3 w-100">
                                                <label
                                                    class="text-title-field">{{ trans('plugins/ecommerce::order.note') }}</label>
                                                <textarea class="ui-text-area" name="description" rows="4"
                                                    placeholder="{{ __('Ghi chú') }}" disabled>{{ $receipt->description }}</textarea>
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
            <div class="ui-layout">
                <div class="flexbox-layout-sections">
                    <div class="flexbox-layout-section-primary mt20">
                        <div class="ui-layout__item receipt">
                            <form action="{{ route('actualout.material-out-confirm.actual-out-material.store', $receipt->id) }}" method="post">
                                @csrf
                                <input type="text" name="proposal_code" value="{{ $receipt->proposal_code }}" hidden>
                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="flexbox-grid-default">
                                            <div class="flexbox-auto-right mr5">
                                                <label class="h4 title-product-main text-no-bold">{{ __('Xác nhận thực xuất nguyên phụ liệu của kho') }}
                                                </label>
                                                <div>
                                                    Xác nhận số lượng để tạo lô hàng
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="table-wrap">
                                            <table class="table-order table-divided receipt-goods">
                                                <tbody>
                                                    @php
                                                    $totalQty = 0;
                                                    @endphp
                                                    @foreach ($receipt->receiptDetail as $orderMaterial)
                                                        <tr class="item__product">
                                                            <td class="width-60-px min-width-60-px vertical-align-t">
                                                                @if($orderMaterial->material_id !== 0)
                                                                <div class="wrap-img">
                                                                    <img
                                                                        class="thumb-image thumb-image-cartorderlist"
                                                                        src="{{ RvMedia::getImageUrl($orderMaterial->material($orderMaterial->material_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $orderMaterial->material($orderMaterial->material_id)->first()?->name }}"
                                                                    >
                                                                </div>
                                                                @else
                                                                <span class="tag__product">Mới</span>
                                                                @endif
                                                            </td>
                                                            <td class="pl5 p-r5">
                                                                {{$orderMaterial->material_name }}
                                                            </td>
                                                            <td class="pl5 p-r5">
                                                                <div class="inline_block">
                                                                    <span>Mã nguyên liệu: <strong>{{ ($orderMaterial->material_code) }}</strong></span>
                                                                </div>
                                                            </td>
                                                            <td class="pl5 p-r5">
                                                                <div class="inline_block">
                                                                    <input type="number" class="widget__batch" name="material[{{ $orderMaterial->id }}][material_price]" value="{{$orderMaterial->batch_code}}" hidden>
                                                                    <span>Mã lô: <strong>{{ ($orderMaterial->batch_code) }}</strong></span>
                                                                </div>
                                                            </td>
                                                            <td class="pl5 p-r5 text-end" width="200px">
                                                                <div class="d-flex align-items-center">
                                                                    <span style="white-space: nowrap;">Số lượng: </span>
                                                                    <input type="number" name="material[{{$orderMaterial->id}}][quantity_default]" value="{{$orderMaterial->quantity}}" hidden>
                                                                    <input type="number" data-default="{{$orderMaterial->quantity}}" class="form-control base__quantity" name="material[{{$orderMaterial->id}}][quantity]" min="0" value="{{$orderMaterial->quantity}}">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="reasoon__receipt">
                                                            <td>
                                                                <label>Lý do: </label>
                                                            </td>
                                                            <td colspan="3">
                                                                <textarea class="form-control" name="material[{{$orderMaterial->id}}][reasoon]" rows="1" placeholder="Vui lòng ghi rõ lý do" ></textarea>
                                                            </td>
                                                        </tr>
                                                        @php
                                                        $totalQty += $orderMaterial->quantity;
                                                        @endphp
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="4" class="text-end h5">
                                                            <strong>Tổng số lượng: </strong> <span class="widget__amount">{{($totalQty)}}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t">
                                        <div class="mt10">
                                            <button class="btn btn-primary" type="submit">{{ __('Xác nhận') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
<script>
    window.addEventListener('load', function(){
        const wp_content = document.querySelector('.ui-layout__item.receipt');
        const wp_receipt_goods = wp_content?.querySelector('.receipt-goods');

        if(wp_receipt_goods)
        {
            const trItem = wp_receipt_goods.querySelectorAll('table tbody tr:nth-child(odd)');
            const reasoon__receipt = wp_receipt_goods.querySelectorAll('table tbody tr:nth-child(even)');

            trItem?.forEach((element, index) => {
                const inputQuantity = element.querySelector('input.base__quantity');

                console.log(element);
                if(inputQuantity)
                {

                    reasoon__receipt[index].style.display = 'none';

                    let qty_default = inputQuantity.dataset.default;

                    inputQuantity?.addEventListener('keyup', function(event){
                        event.preventDefault();

                        if(event.target.value == qty_default)
                        {
                            reasoon__receipt[index].style.display = 'none';
                        }else{
                            reasoon__receipt[index].style.display = 'table-row';
                        }
                    })
                }
            });
        }
    })

</script>
