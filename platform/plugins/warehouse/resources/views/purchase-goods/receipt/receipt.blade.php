@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
@php
use Botble\Warehouse\Enums\MaterialReceiptStatusEnum;
@endphp
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
    <div style="max-width: 800px; margin: 0 auto;" id="main-order-content">
        <div class="ui-layout">
            <div class="flexbox-layout-sections">
                <div class="flexbox-layout-section-primary mt20">
                    <div class="ui-layout__item">
                        <form action="{{ route('receipt-purchase-goods.confirm.store', $receipt->id) }}" method="post">
                            @csrf
                            <input type="text" name="proposal_code" value="{{ $receipt->proposal_id }}" hidden>
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="flexbox-grid-default">
                                        <div class="flexbox-auto-right mr5">
                                            <label $class="title-product-main text-no-bold">{{ __('Phiếu mua hàng') }} <strong>{{$receipt->proposal->code }}</strong>
                                                - Người duyệt: <strong>{{$receipt->invoice_issuer_name }}</strong>
                                            </label>
                                            <div>
                                                Kho: <strong>{{$receipt->warehouse_name }}</strong>
                                            </div>
                                            <div>
                                                Tiêu đề: {{$receipt->title }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided">
                                            <tbody>
                                                @php
                                                $totalQuantity=0;
                                                @endphp
                                                @foreach ($receipt->receiptDetail as $orderMaterial)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            <div class="wrap-img">
                                                                <img
                                                                    class="thumb-image thumb-image-cartorderlist"
                                                                    src="{{ RvMedia::getDefaultImage() }}"
                                                                    alt="{{ $orderMaterial->material_name }}"
                                                                >
                                                            </div>
                                                        </td>
                                                        <td class="pl5 p-r5 min-width-200-px">
                                                            {{$orderMaterial->material_name }}
                                                        </td>
                                                        <td class="pl5 p-r5">
                                                            Mã: {{$orderMaterial->material_code }}
                                                        </td>
                                                        <td class="pl5 p-r5">
                                                            {{$orderMaterial->supplier_name }}
                                                        </td>
                                                        <td class="pl5 p-r5">
                                                            <span class="check__highlight">Số lượng: {{ $orderMaterial->material_quantity }}</span>
                                                        </td>
                                                    </tr>
                                                    @php $totalQuantity+= $orderMaterial->material_quantity
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="5" class="text-end h5">
                                                        <strong>Tổng số lượng: </strong> <span class="widget__amount">{{($totalQuantity)}}</span>
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
                                        @php
                                            $inputDate = $receipt->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                            $formattedDate = date('d/m/Y', strtotime($inputDate));
                                        @endphp
                                        <div class="flexbox-auto-right pl5">
                                            <div class="row">
                                                <div class="col-lg-6 col-sm-12">
                                                    <div class="py-3">
                                                        <label class="text-title-field">{{ __('Ngày dự kiến') }}</label>
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
                                    @if($receipt->status != MaterialReceiptStatusEnum::APPOROVED)
                                    <div class="mt10">
                                        <button class="btn btn-primary" type="submit">{{ __('Xác nhận') }}</button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
<script>
    window.addEventListener('load', function(){
        const tableReceipt = document.querySelector('.table-order.table-divided');
        const itemProduct = tableReceipt?.querySelectorAll('.item__product');

        if(itemProduct)
        {
            const wg_tax = tableReceipt.querySelector('.widget__tax__amount')
            const wg_amount = tableReceipt.querySelector('.widget__amount')

            itemProduct.forEach(product => {
                const wg_price = product.querySelector('.widget__price')
                const wg_quantity = product.querySelector('.widget__quantity')
                const wg_totalPrice = product.querySelector('.widget__total__price')

                const inputTotalPrice = product.querySelector('.value__total__price')

                if(wg_price && wg_quantity && wg_totalPrice && wg_tax && wg_amount)
                {
                    getTotalAmount(tableReceipt)
                    wg_quantity.addEventListener('keyup', function(event){
                        let curQty = event.target.value;

                        let totalPrice = wg_price.value * curQty;

                        inputTotalPrice.value = totalPrice;
                        wg_totalPrice.textContent = format_price(totalPrice);

                        getTotalAmount(tableReceipt)
                    })

                    wg_quantity.addEventListener('change', function(event){
                        let curQty = event.target.value;

                        let totalPrice = wg_price.value * curQty;

                        inputTotalPrice.value = totalPrice;
                        wg_totalPrice.textContent = format_price(totalPrice);
                        getTotalAmount(tableReceipt)
                    })
                }

            });
        }
    })
    function format_price(price) {
        const formatted = price.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
        return formatted;
    }

    function getTotalAmount(table)
    {
        const itemProduct = table?.querySelectorAll('.item__product');

        if(itemProduct)
        {
            const wg_tax = table.querySelector('.widget__tax__amount')
            const wg_amount = table.querySelector('.widget__amount')
            const ac_amount = table.querySelector('.ac_amount')

            let amount = 0;

            itemProduct.forEach(product => {
                const wg_price = product.querySelector('.widget__price')
                const wg_quantity = product.querySelector('.widget__quantity')
                const wg_totalPrice = product.querySelector('.widget__total__price')

                const inputTotalPrice = product.querySelector('.value__total__price')

                amount += inputTotalPrice.value*1;
            });

            amount += wg_tax.dataset.tax*1;
            ac_amount.value = amount;
            wg_amount.textContent = format_price(amount)

            return amount
        }
    }

</script>
