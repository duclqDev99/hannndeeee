@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\Warehouse\Enums\MaterialReceiptStatusEnum;
    @endphp
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
    </style>
   
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
                                            <label class="h4 title-product-main text-no-bold">{{ __('Phiếu nhập kho') }}
                                                <strong>{{ $receipt->proposal->proposal_code }}</strong> - Người đề xuất:
                                                <strong>{{ $receipt->invoice_issuer_name }},</strong>
                                                lúc <span>{{ date('d/m/Y', strtotime($receipt->created_at)) }}</span>
                                            </label>
                                            <div>
                                                
                                            </div>
                                            <div>
                                                @if ($receipt->is_from_supplier === 0)
                                                    Nhập từ: <strong>{{ $receipt->wh_departure_name }} <i
                                                            class="fa-solid fa-arrow-right"></i>
                                                        {{ $receipt->warehouse_name }}</strong>
                                                @else
                                                    Kho: <strong>{{ $receipt->warehouse_name }}</strong>
                                                @endif
                                            </div>
                                            <div>
                                                Tiêu đề: {{ $receipt->title }}
                                            </div>
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
                                                @foreach ($receipt->receiptDetail as $orderMaterial)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if ($orderMaterial->material_id !== 0)
                                                                <div class="wrap-img">
                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                        style="max-width: 70px;"
                                                                        src="{{ RvMedia::getImageUrl($orderMaterial->material($orderMaterial->material_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $orderMaterial->material($orderMaterial->material_id)->first()?->name }}">
                                                                </div>
                                                            @else
                                                                <span class="tag__product">Mới</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 min-width-200-px">
                                                            {{$orderMaterial->material_name }}
                                                            <br>
                                                            <span>Mã: <strong>{{ ($orderMaterial->material_code) }}</strong></span>
                                                            {{ $orderMaterial->material_name }}
                                                            <input type="number" class="widget__price"
                                                                name="material[{{ $orderMaterial->id }}][material_price]"
                                                                value="{{ $orderMaterial->material_price }}" hidden>
                                                        </td>
                                                        @if($receipt->is_from_supplier === 1)
                                                        <td class="p-3" width="200px">
                                                            <div class="d-flex align-items-center">
                                                                {{ $orderMaterial->supplier_name }}
                                                            </div>
                                                        </td>
                                                        @endif
                                                        <td class="p-3 text-end" width="120px">
                                                            <input type="number"
                                                                name="material[{{ $orderMaterial->id }}][quantity]"
                                                                class="form-control widget__quantity"
                                                                value="{{ $orderMaterial->material_quantity }}"
                                                                min="0" placeholder="0" hidden>
                                                            <span>Số lượng: {{ $orderMaterial->material_quantity }}</span>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $totalQty += $orderMaterial->material_quantity;
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
                                                <label class="text-title-field">Ghi chú: </label>
                                                <textarea class="form-control textarea-auto-height" name="description" rows="4"
                                                    placeholder="{{ __('Ghi chú') }}" disabled>{{ $receipt->description }}</textarea>
                                            </div>
                                        </div>
                                        @php
                                            $inputDate = $receipt->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                            $formattedDate = date('d/m/Y', strtotime($inputDate));
                                        @endphp
                                        <div class="col-lg-6 col-md-12">
                                            <div class="py-3">
                                                <label class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                <input class="form-control flatpickr-input" value="{{ $formattedDate }}"
                                                    data-date-format="d-m-Y" v-pre="1" placeholder="d-m-Y"
                                                    data-input="" readonly="readonly" name="expected_date"
                                                    type="text" id="expected_date" aria-invalid="false"
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
            <div class="ui-layout actual__receipt">
                <div class="flexbox-layout-sections">
                    <div class="flexbox-layout-section-primary mt20">
                        <div class="card ui-layout__item receipt">
                            <form action="{{ route('material-receipt-confirm.confirm.store', $receipt->id) }}"
                                method="post">
                                @csrf
                                <input type="text" name="proposal_code" value="{{ $receipt->proposal_code }}" hidden>
                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="card-header flexbox-grid-default">
                                            <div class="flexbox-auto-right mr5">
                                                <label
                                                    class="h4 title-product-main text-no-bold">{{ __('Xác nhận thực nhập nguyên phụ liệu của kho') }}
                                                </label>
                                                <div>
                                                    Người đề xuất: <strong>{{ $receipt->invoice_issuer_name }}</strong>
                                                </div>
                                                <div>
                                                    Xác nhận số lượng để tạo lô hàng
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="table-wrap">
                                            <table
                                                class="table-order table-divided table-vcenter card-table receipt-goods w-100">
                                                <tbody>
                                                    @php
                                                        $totalQty = 0;
                                                    @endphp
                                                    @foreach ($receipt->receiptDetail as $orderMaterial)
                                                        <tr class="item__product">
                                                            <td class="width-60-px min-width-60-px vertical-align-t">
                                                                @if ($orderMaterial->material_id !== 0)
                                                                    <div class="wrap-img">
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            style="max-width: 70px;"
                                                                            src="{{ RvMedia::getImageUrl($orderMaterial->material($orderMaterial->material_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            alt="{{ $orderMaterial->material($orderMaterial->material_id)->first()?->name }}">
                                                                    </div>
                                                                @else
                                                                    <span class="tag__product">Mới</span>
                                                                @endif
                                                            </td>
                                                            <td class="p-3 min-width-200-px">
                                                                {{$orderMaterial->material_name }}
                                                                <br> <span>Mã nguyên liệu: <strong>{{ ($orderMaterial->material_code) }}</strong></span>
                                                            </td>
                                                            @if($receipt->is_from_supplier === 1)
                                                            <td class="p-3" width="200px">
                                                                <div class="d-flex align-items-center">
                                                                    {{ $orderMaterial->supplier_name }}
                                                                </div>
                                                            </td>
                                                            @endif
                                                            <td class="p-3 text-end" width="200px">
                                                                <div class="d-flex align-items-center">
                                                                    <span style="white-space: nowrap;">Số lượng: </span>
                                                                    <input type="number"
                                                                        name="material[{{ $orderMaterial->id }}][quantity_default]"
                                                                        value="{{ $orderMaterial->material_quantity }}"
                                                                        hidden>
                                                                    <input type="number"
                                                                        data-default="{{ $orderMaterial->material_quantity }}"
                                                                        class="form-control base__quantity"
                                                                        name="material[{{ $orderMaterial->id }}][quantity]"
                                                                        min="0"
                                                                        value="{{ $orderMaterial->material_quantity }}">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="reasoon__receipt" style="display: none;">
                                                            <td>
                                                                <label>Lý do: </label>
                                                            </td>
                                                            <td colspan="3">
                                                                <textarea class="form-control" name="material[{{ $orderMaterial->id }}][reasoon]" rows="1"
                                                                    placeholder="Vui lòng ghi rõ lý do"></textarea>
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $totalQty += $orderMaterial->material_quantity;
                                                        @endphp
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="5" class="text-end h5">
                                                            <strong>Tổng số lượng: </strong> <span
                                                                class="widget__total_quantity">{{ $totalQty }}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @if ($receipt->status != MaterialReceiptStatusEnum::APPOROVED)
                                        <div class="card-body pd-all-20 p-none-t">
                                            <div class="mt10">
                                                <button class="btn btn-primary"
                                                    type="submit">{{ __('Xác nhận') }}</button>
                                            </div>
                                        </div>
                                    @endif
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
    window.addEventListener('load', function() {
        const wp_content = document.querySelector('.ui-layout__item.receipt');
        const wp_receipt_goods = wp_content?.querySelector('.receipt-goods');

        if (wp_receipt_goods) {
            const trItem = wp_receipt_goods.querySelectorAll('table tbody tr:nth-child(odd)');
            const reasoon__receipt = wp_receipt_goods.querySelectorAll('table tbody tr:nth-child(even)');

            trItem?.forEach((element, index) => {
                const inputQuantity = element.querySelector('input.base__quantity');
                if (inputQuantity && reasoon__receipt) {
                    console.log(reasoon__receipt[index]);
                    const textarea = reasoon__receipt[index].querySelector('textarea');

                    reasoon__receipt[index].style.display = 'none';

                    let qty_default = inputQuantity.dataset.default;

                    inputQuantity?.addEventListener('keyup', function(event) {
                        event.preventDefault();
        
                        if(event.target.value == qty_default)
                        {
                            reasoon__receipt[index].style.display = 'none';
                            textarea.setAttribute('required', false);
                        } else {
                            reasoon__receipt[index].style.display = 'table-row';
                            textarea.setAttribute('required', true);
                        }
                    })

                    inputQuantity?.addEventListener('change', function(event) {
                        event.preventDefault();
        
                        if(event.target.value == qty_default)
                        {
                            reasoon__receipt[index].style.display = 'none';
                            textarea.setAttribute('required', false);
                        } else {
                            reasoon__receipt[index].style.display = 'table-row';
                            textarea.setAttribute('required', true);
                        }
                    })
                }
            });
        }

        const eActualReceipt = document.querySelector('.actual__receipt');
        if (eActualReceipt) {
            const itemProduct = eActualReceipt?.querySelectorAll('.item__product');

            if (itemProduct) {
                const widget__total_quantity = eActualReceipt.querySelector('.widget__total_quantity')

                itemProduct.forEach(product => {
                    const wg_quantity = product.querySelector('.base__quantity')

                    if (wg_quantity) {
                        getTotalQty(eActualReceipt)
                        wg_quantity.addEventListener('keyup', function(event) {
                            let curQty = event.target.value;
                            getTotalQty(eActualReceipt)
                        })

                        wg_quantity.addEventListener('change', function(event) {
                            let curQty = event.target.value;
                            getTotalQty(eActualReceipt)
                        })
                    }

                });
            }
        }
    })

    function getTotalQty(table) {
        const itemProduct = table?.querySelectorAll('.item__product');

        if (itemProduct) {
            const wg_totalPrice = table.querySelector('.widget__total_quantity')
            let amount = 0;

            itemProduct.forEach(product => {
                const wg_quantity = product.querySelector('.base__quantity');
                amount += wg_quantity.value * 1;
            });

            wg_totalPrice.textContent = amount
        }
    }
</script>
