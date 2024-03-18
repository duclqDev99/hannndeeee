@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
    use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    use Botble\HubWarehouse\Models\Warehouse;
    use Botble\HubWarehouse\Models\QuantityProductInStock;
    use Botble\ProductQrcode\Enums\QRStatusEnum;
    use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
    use Botble\Agent\Models\AgentProduct;

@endphp
@section('content')
    <div class="w-75 p-3 mx-auto" id="main-order-content" style="width: 750px;">
        <div class="ui-layout">
            <div class="flexbox-layout-sections">
                <div class="flexbox-layout-section-primary mt20">
                    <div class="card ui-layout__item">
                        <form id="approve" action="{{ route('agent-proposal-issue.approve', $proposal) }}" method="post">
                            @csrf
                            <input type="text" name="proposal_code" value="{{ $proposal->proposal_code }}" hidden>
                            <input type="text" name="warehouse_id" value="{{ $proposal->warehouse_id }}" hidden>
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="card-header justify-content-between">
                                        <div class="flexbox-auto-right mr5">
                                            <label
                                                $class="title-product-main text-no-bold">{{ __('Duyệt đơn yêu cầu trả hàng') }}
                                                từ:
                                                <strong>{{ $proposal->warehouse_name }} -
                                                    {{ $proposal->warehouseIssue->agent->name }}
                                            </label>
                                            <div>
                                                Mã phiếu:
                                                <strong>{{ BaseHelper::clean(get_proposal_issue_product_code($proposal->proposal_code)) }}</strong>
                                            </div>
                                            <div>
                                                Người đề xuất: <strong>{{ $proposal->invoice_issuer_name }}</strong>
                                            </div>
                                            <div>
                                                Tiêu đề: {{ $proposal->title }}
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="flexbox-auto-right mr5">

                                        <div class="row align-center" style="margin-left: 20px">
                                            <div class="form-group col-md-3">
                                                <label for="hub_id">Hub nhận:</label>
                                                <select name="hub_id" id="hub_id" class="select-full form-control">
                                                    <option value="0">Chọn hub</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="warehouse_id">Kho nhận:</label>
                                                <select name="warehouse_id" id="warehouse_id"
                                                    class="select-full form-control">
                                                    <option value="0">Chọn kho</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided table-vcenter card-table" width="100%">
                                            <tbody>
                                                @foreach ($proposal->proposalAgentIssueDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t"
                                                            width="20%">
                                                            @if ($orderProduct->id)
                                                                <div class="wrap-img">
                                                                    <img style="margin-top: 20px"
                                                                        class="thumb-image thumb-image-cartorderlist"
                                                                        src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}"
                                                                        width="100px" height="100px">
                                                                </div>
                                                            @endif
                                                        </td>

                                                        <td class="pl5 p-r5 min-width-200-px">
                                                            {{ $orderProduct->product_name }}
                                                            @php
                                                                echo $orderProduct->size !== '' ? ' - (Màu: ' . $orderProduct->color . '- Size: ' . $orderProduct->size . ' ) ' : '';
                                                            @endphp

                                                            <input type="text" class="widget__price"
                                                                name="product[{{ $orderProduct->product_id }}][size]"
                                                                value="{{ $orderProduct->size }}" hidden>
                                                            <input type="text" class="widget__price"
                                                                name="product[{{ $orderProduct->product_id }}][color]"
                                                                value="{{ $orderProduct->color }}" hidden>
                                                        </td>

                                                        <td class="pl5 p-r5">
                                                            <div class="inline_block">
                                                                <span>Mã:
                                                                    <strong>{{ $orderProduct->sku }}</strong></span>
                                                            </div>
                                                        </td>

                                                        <td class="pl5 p-r5">
                                                            @php
                                                                $quantity = AgentProduct::where([
                                                                    'product_id' => $orderProduct->product_id,
                                                                    'warehouse_id' => $proposal->warehouse_issue_id,
                                                                ])?->first()?->quantity_qrcode;
                                                            @endphp

                                                            <span style="white-space: nowrap;">Tồn kho:
                                                                {{ $quantity ?: 0 }} <input type="number"
                                                                    class="quantityStock"
                                                                    name="product[{{ $orderProduct->product_id }}][quantityStock]"
                                                                    value="{{ $quantity }}" hidden> </span>
                                                        </td>
                                                        <td class="pl5 text-center">
                                                            <input type="number" class="price"
                                                                name="product[{{ $orderProduct->product_id }}][price]"
                                                                value="{{ $orderProduct->price }}" hidden>

                                                        </td>
                                                        <td class="pl5 p-r5" width="100px">
                                                            <div class="d-flex align-items-center">
                                                                <input type="number"
                                                                    name="product[{{ $orderProduct->product_id }}][quantity]"
                                                                    class="form-control input_quantity"
                                                                    value="{{ $orderProduct->quantity }}" min="1"
                                                                    max="{{ $quantity }}" placeholder="0" required>
                                                                <input type="hidden"
                                                                    name="product[{{ $orderProduct->product_id }}][is_batch]"
                                                                    class="form-control"
                                                                    value="{{ $orderProduct->is_batch }}">
                                                        </td>


                                                    </tr>
                                                @endforeach

                                                <tr>
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
                                                        <textarea class="form-control textarea-auto-height" name="description" rows="4"
                                                            placeholder="{{ __('Ghi chú') }}">{{ $proposal->description }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="py-3">
                                                        <label class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                        @php
                                                            $inputDate = $proposal->expected_date;
                                                            $formattedDate = date('d/m/Y', strtotime($inputDate));
                                                        @endphp
                                                        <div class="input-group datepicker">
                                                            <input class="form-control flatpickr-input"
                                                                data-date-format="d-m-Y" v-pre="1"
                                                                placeholder="d-m-Y" data-input="" readonly="readonly"
                                                                name="expected_date" type="text"
                                                                value="{{ $formattedDate }}" id="expected_date"
                                                                aria-invalid="false"
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
                                        <div class="mt10">
                                            <button class="btn btn-primary" type="button"
                                                id="openApproveModel">{{ __('Duyệt đơn') }}</button>
                                            <button class="btn btn-danger" type="button"
                                                id="denyButton">{{ __('Từ chối') }}</button>
                                        </div>
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
                                            action="{{ route('agent-proposal-issue.denied', $proposal->id) }}">
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
