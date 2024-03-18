@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\Warehouse\Models\QuantityMaterialStock;
@endphp
@section('content')
    <div class="w-75 p-3 mx-auto" id="main-order-content" style="width: 750px;">
        <div class="ui-layout">
            <div class="flexbox-layout-sections">
                <div class="flexbox-layout-section-primary mt20">
                    <div class="card ui-layout__item">
                        <form action="{{ route('proposal-goods-issue.receipt', $proposal->id) }}" method="post"
                            id = "approve">
                            @csrf
                            <input type="text" name="proposal_code" value="{{ $proposal->proposal_code }}" hidden>
                            <input type="text" name="warehouse_id" value="{{ $proposal->warehouse_id }}" hidden>
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="card-header justify-content-between">
                                        <div class="flexbox-auto-right mr5">

                                            <label
                                                $class="title-product-main text-no-bold">{{ __('Phiếu đề xuất xuất kho') }}
                                                <strong>{{ $proposal->proposal_code }}</strong> -
                                                Xuất từ: <strong>{{ $proposal->warehouse_name }} <i
                                                        class="fa-solid fa-arrow-right"></i>
                                                    {{ $proposal->warehouse->name }}</strong>
                                            </label>
                                            <div>
                                                Mã đơn hàng: <strong>{{ $proposal->general_order_code }}</strong>
                                            </div>
                                            <div>
                                                Người đề xuất: <strong>{{ $proposal->invoice_issuer_name }}</strong>
                                            </div>
                                            <div>
                                                Tiêu đề: {{ $proposal->title }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided table-vcenter card-table" width="100%">
                                            <tbody>
                                                @foreach ($proposal->proposalOutDetail as $orderMaterial)
                                                    @php
                                                        $material_id = $orderMaterial->material($orderMaterial->material_code)->first()?->id;
                                                        $quantityStock = QuantityMaterialStock::where(['material_id' => $material_id, 'warehouse_id' => $proposal->warehouse_id])->first()->quantity;
                                                    @endphp

                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t"
                                                            width="20%">
                                                            @if ($orderMaterial->material_code)
                                                                <div class="wrap-img">
                                                                    <img style="margin-top: 20px"
                                                                        class="thumb-image thumb-image-cartorderlist"
                                                                        src="{{ RvMedia::getImageUrl($orderMaterial->material($orderMaterial->material_code)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $orderMaterial->material($orderMaterial->material_code)->first()?->name }}"
                                                                        width="60px" height="60px">
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="pl5 p-r5 min-width-200-px">
                                                            {{ $orderMaterial->material_name }}
                                                        </td>

                                                        <td class="pl5 p-r5 min-width-200-px">
                                                            {{ RvMedia::getImageUrl($orderMaterial->image, 'post-small') }}
                                                        </td>

                                                        <td class="pl5 p-r5">
                                                            <div class="inline_block">
                                                                <input type="number" class="widget__price"
                                                                    name="material[{{ $orderMaterial->id }}][material_price]"
                                                                    value="{{ $orderMaterial->material_price }}" hidden>

                                                                <span>Mã:
                                                                    <strong>{{ $orderMaterial->material_code }}</strong></span>
                                                            </div>
                                                        </td>
                                                        <td class="pl5 text-center">
                                                            <input type="hidden"
                                                                name="material[{{ $orderMaterial->id }}][quantityStock]"
                                                                value="{{ $quantityStock }}">
                                                            <span>Tồn kho:
                                                                {{ $quantityStock }}</span>
                                                        </td>
                                                        <td class="pl5 text-center">
                                                            <span>{{ format_price($orderMaterial->material_price) }} /
                                                                {{ $orderMaterial->material_unit }}</span>
                                                        </td>

                                                        <td>&nbsp X &nbsp</td>
                                                        <td class="pl5 p-r5" width="200px">
                                                            <div class="d-flex align-items-center">
                                                                <input type="number"
                                                                    name="material[{{ $orderMaterial->id }}][quantity]"
                                                                    class="form-control widget__quantity"
                                                                    value="{{ $orderMaterial->material_quantity }}"
                                                                    min="0" max="{{ $quantityStock }}"
                                                                    placeholder="0" required>
                                                        </td>


                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <input type="number" class="value__total__price"
                                                        value="{{ $orderMaterial->material_price * $orderMaterial->material_quantity }}"
                                                        hidden>
                                                    <td colspan="12" class="text-end h5">
                                                        <strong>Tống tiền: </strong> <span class="widget__totl__amount"
                                                            data-tax="{{ $proposal->total_amount }}">{{ format_price($proposal->total_amount) }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-end h5">
                                                        <input type="number" name="ac_amount"
                                                            value="{{ $proposal->amount }}" class="ac_amount" hidden>

                                                    </td>
                                                    <td colspan="6" class="text-end h5">
                                                        <strong>Tống số lượng: </strong> <span class="widget__quantity"
                                                            data-tax="{{ $proposal->quantity }}">{{ $proposal->quantity }}</span>
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
                                                    <div class="py-3 w-100">
                                                        <label class="text-title-field">Ghi chú</label>
                                                        <textarea class="form-control textarea-auto-height" name="description" rows="4" placeholder="{{ __('Ghi chú') }}">{{ $proposal->description }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="py-3">
                                                        <label class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                        @php
                                                            $inputDate = $proposal->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                                            $formattedDate = date('d/m/Y', strtotime($inputDate));
                                                        @endphp
                                                        <div class="input-group datepicker">
                                                            <input class="form-control flatpickr-input"
                                                                data-date-format="d-m-Y" v-pre="1" placeholder="d-m-Y"
                                                                data-input="" readonly="readonly" name="expected_date"
                                                                type="text" value="{{ $formattedDate }}"
                                                                id="expected_date" aria-invalid="false"
                                                                aria-describedby="expected_date-error">
                                                            <button class="btn btn-icon" type="button"
                                                                data-toggle="data-toggle">
                                                                <span class="icon-tabler-wrapper icon-left">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-calendar"
                                                                        width="24" height="24" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none"></path>
                                                                        <path
                                                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z">
                                                                        </path>
                                                                        <path d="M16 3v4"></path>
                                                                        <path d="M8 3v4"></path>
                                                                        <path d="M4 11h16"></path>
                                                                        <path d="M11 15h1"></path>
                                                                        <path d="M12 15v3"></path>
                                                                    </svg>


                                                                </span>


                                                            </button>
                                                            <button class="btn btn-icon   text-danger" type="button"
                                                                data-clear="data-clear">
                                                                <span class="icon-tabler-wrapper icon-left">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-x"
                                                                        width="24" height="24" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none"></path>
                                                                        <path d="M18 6l-12 12"></path>
                                                                        <path d="M6 6l12 12"></path>
                                                                    </svg>


                                                                </span>


                                                            </button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt10">
                                        <button class="btn btn-primary" type="button">{{ __('Duyệt đơn') }}</button>
                                        <button class="btn btn-danger" type="button"
                                            id="denyButton">{{ __('Từ chối') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="modal" id="denyModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-status bg-danger"></div>


                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="modal-body text-center py-4">
                                        <div class="mb-2">
                                            <span class="icon-tabler-wrapper icon-lg text-danger">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-alert-triangle" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M12 9v4"></path>
                                                    <path
                                                        d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                                    </path>
                                                    <path d="M12 16h.01"></path>
                                                </svg>


                                            </span>
                                        </div>

                                        <h3>Từ chối duyệt đơn hàng này</h3>

                                    </div>
                                    <div class="modal-body">
                                        <form id="denyForm" method="post"
                                            action="{{ route('proposal-goods-issue.denied', $proposal->id) }}">
                                            @csrf
                                            <label for="denyReason">Lý do từ chối:</label>
                                            <textarea class="form-control required" id="denyReason" name="denyReason" rows="3"></textarea>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" class="btn btn-danger" id="denySubmit">Từ
                                                    chối</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $("#denyButton").on("click", function() {
            $("#denyModal").modal("show");
        });
        $("#denySubmit").on("click", function(e) {
            e.preventDefault();
            var denyReason = $("#denyReason").val();
            if (denyReason.trim() === "") {
                alert("Vui lòng nhập lý do từ chối.");
                return;
            }
            $("#denyForm").submit();
        });
    </script>
@stop
<script>
    window.addEventListener('load', function() {
        const tableReceipt = document.querySelector('.table-order.table-divided');
        const itemProduct = tableReceipt?.querySelectorAll('.item__product');

        if (itemProduct) {
            const wg_tax = tableReceipt.querySelector('.widget__tax__amount')
            const wg_amount = tableReceipt.querySelector('.widget__amount')

            itemProduct.forEach(product => {
                const wg_price = product.querySelector('.widget__price')
                const wg_quantity = product.querySelector('.widget__quantity')
                const wg_totalPrice = product.querySelector('.widget__total__price')

                const inputTotalPrice = product.querySelector('.value__total__price')

                if (wg_price && wg_quantity && wg_totalPrice && wg_tax && wg_amount) {
                    getTotalAmount(tableReceipt)
                    wg_quantity.addEventListener('keyup', function(event) {
                        let curQty = event.target.value;

                        let totalPrice = wg_price.value * curQty;

                        inputTotalPrice.value = totalPrice;
                        wg_totalPrice.textContent = format_price(totalPrice);

                        getTotalAmount(tableReceipt)
                    })

                    wg_quantity.addEventListener('change', function(event) {
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
        const formatted = price.toLocaleString('vi-VN', {
            style: 'currency',
            currency: 'VND'
        });
        return formatted;
    }

    function getTotalAmount(table) {
        const itemProduct = table?.querySelectorAll('.item__product');

        if (itemProduct) {
            const wg_tax = table.querySelector('.widget__tax__amount')
            const wg_amount = table.querySelector('.widget__amount')
            const ac_amount = table.querySelector('.ac_amount')

            let amount = 0;

            itemProduct.forEach(product => {
                const wg_price = product.querySelector('.widget__price')
                const wg_quantity = product.querySelector('.widget__quantity')
                const wg_totalPrice = product.querySelector('.widget__total__price')

                const inputTotalPrice = product.querySelector('.value__total__price')

                amount += inputTotalPrice.value * 1;
            });

            amount += wg_tax.dataset.tax * 1;
            ac_amount.value = amount;
            wg_amount.textContent = format_price(amount)

            return amount
        }
    }
</script>
