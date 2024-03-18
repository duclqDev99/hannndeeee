@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
    use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    use Botble\HubWarehouse\Models\Warehouse;
    use Botble\HubWarehouse\Models\QuantityProductInStock;
    use Botble\ProductQrcode\Enums\QRStatusEnum;
    use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;

@endphp
@section('content')
    <style>
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

        .card-notify-year.blue {
            background: rgb(74, 74, 236);
        }
    </style>
    <div class="col-lg-10 col-md-12 mx-auto" id="main-order-content">
        <div class="ui-layout">
            <div class="flexbox-layout-sections">
                <div class="flexbox-layout-section-primary mt20">
                    <div class="card ui-layout__item">
                        <form id="approve" action="{{ route('proposal-hub-issue.approve', $proposal->id) }}" method="post">
                            @csrf
                            <input type="text" name="proposal_  code" value="{{ $proposal->proposal_code }}" hidden>
                            <input type="text" name="warehouse_id" value="{{ $proposal->warehouse_id }}" hidden>
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="card">
                                        <div class="card-header">
                                            <div>
                                                <h2 class="title">Thông tin đơn đề xuất xuất kho
                                                    {{ BaseHelper::clean(get_proposal_issue_product_code($proposal->proposal_code)) }}
                                                </h2>
                                                <div>
                                                    <h3>
                                                        Mục đích xuất kho: {{ $proposal->title }}
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <label>Kho xuất:</label>
                                                        <strong>{{ $proposal->warehouse_name }} -
                                                            {{ $proposal->warehouseIssue->hub->name }}</strong>
                                                    </div>
                                                    <div class="info-group">
                                                        <label>Kho nhận:</label>
                                                        <strong>
                                                            @if ($proposal->is_warehouse == 6)
                                                                Xuất đi giải
                                                            @else
                                                                {{ $proposal->warehouse->name }} -
                                                                {{ $proposal->warehouse?->hub?->name ?:
                                                                    ($proposal->warehouse?->agent?->name ?:
                                                                        ($proposal->warehouse?->showroom?->name ?:
                                                                            ($proposal->warehouse?->saleWarehouse?->name ?:
                                                                                ''))) }}
                                                            @endif
                                                        </strong>
                                                    </div>
                                                    <div class="info-group">
                                                        <label>Mã đơn hàng:</label>
                                                        <strong>{{ $proposal->general_order_code ?: '—' }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <label>Người đề xuất:</label>
                                                        <strong>{{ $proposal->invoice_issuer_name }}</strong>
                                                    </div>
                                                    <div class="info-group">
                                                        Ngày tạo: {{ date('d/m/Y', strtotime($proposal->created_at)) }}

                                                    </div>
                                                    <div>
                                                        Đề xuất từ:
                                                        <strong>
                                                            {{ !$proposal->proposal_receipt_id ? 'Hub' : ($proposal->warehouse_type == \Botble\Showroom\Models\ShowroomWarehouse::class ? 'Showroom' : ($proposal->warehouse_type == \Botble\Agent\Models\AgentWarehouse::class ? 'Đại lý' : '')) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            @if ($proposal->is_warehouse == 5)
                                                <div>
                                                    @php
                                                        $policy = $proposal->policy;
                                                    @endphp
                                                    <div>
                                                        <div class="text h2" style="color: #ff4444"> Chính sách giảm giá
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div>
                                                            <div class="row">
                                                                <div class="col-lg-6 col-12 col-md-6">
                                                                    <p><strong>Tên chính sách:</strong> {{ $policy->name }}
                                                                    </p>
                                                                    <p><strong>Ngày bắt đầu:</strong>
                                                                        {{ $policy->start_date }}</p>
                                                                    <p><strong>Số lượng:</strong>
                                                                        {{ $policy->quantity > 0 ? $policy->quantity : 'Không giới hạn' }}
                                                                    </p>
                                                                    <p><strong>Giảm: </strong>{{ $policy->value }}
                                                                        {{ $policy->type_option == 'amount' ? 'VNĐ' : '%' }}
                                                                    </p>
                                                                </div>
                                                                <div class="col-lg-6 col-12">
                                                                    <p><strong>Mã chính sách:</strong> {{ $policy->code }}
                                                                    </p>
                                                                    <p><strong>Ngày kết thúc:</strong>
                                                                        {{ $policy->end_date ?? 'Không giới hạn' }}</p>
                                                                        Sản phẩm có
                                                                        <strong>{{ $policy->type_time == 'date_production' ? ' ngày sản xuất' : ' ngày nhập kho' }}
                                                                        </strong> sau
                                                                        <strong>
                                                                            {{ $policy->time_active }}
                                                                            {{ $policy->type_date_active == 'date' ? ' ngày' : ($policy->type_date_active == 'month' ? 'tháng' : 'năm') }}</strong>
                                                                    </p>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="card-notify-year red">Phiếu đề xuất xuất kho</span>
                                    </div>
                                </div>
                                @php
                                    $totalQty = 0;
                                    $products = [];
                                    $hasBatchProducts = false;
                                    $hasNonBatchProducts = false;
                                @endphp

                                @foreach ($proposal->proposalHubIssueDetail as $orderProduct)
                                    @if ($orderProduct->is_batch == 1)
                                        @php
                                            $hasBatchProducts = true;
                                        @endphp
                                    @endif
                                    @if ($orderProduct->is_batch == 0)
                                        @php
                                            $hasNonBatchProducts = true;
                                        @endphp
                                    @endif
                                @endforeach
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        @if ($hasBatchProducts)
                                            <div class="col-md-12">
                                                <h2 style="margin: 20px 0 0 15px">Xuất theo lô</h3>
                                                    <table
                                                        class="table-order table-divided table-vcenter card-table col-12">
                                                        <tbody>
                                                            @foreach ($proposal->proposalHubIssueDetail as $orderProduct)
                                                                @if ($orderProduct->is_batch == 1)
                                                                    <tr class="item__product">
                                                                        <td class="vertical-align-t" width="20%">
                                                                            <div class="wrap-img">
                                                                                <img style="margin-top: 20px"
                                                                                    class="thumb-image thumb-image-cartorderlist"
                                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                    alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}"
                                                                                    width="100px" height="100px">
                                                                            </div>
                                                                        </td>
                                                                        <td class="pl5 p-r5" width="40%">
                                                                            {{ $orderProduct->product_name }}
                                                                        </td>

                                                                        <td class="pl5 p-r5" width="20%">
                                                                            @php
                                                                                $quantity =
                                                                                    $orderProduct?->batch?->quantity;
                                                                            @endphp
                                                                            Lô: <strong>
                                                                                {{ $orderProduct?->batch?->batch_code }}
                                                                            </strong>

                                                                        </td>
                                                                        <td class="pl5 p-r5 text-center" width="35%">
                                                                            <input type="number"
                                                                                name="batch[{{ $orderProduct?->batch_id }}][quantity]"
                                                                                value="{{ $quantity }}" hidden />
                                                                            <input type="text"
                                                                                name="batch[{{ $orderProduct?->batch_id }}][product_id]"
                                                                                value="{{ $orderProduct->product_id }}"
                                                                                hidden />
                                                                            Số lượng trong lô:
                                                                            {{ $orderProduct->quantity }} sản phẩm
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                            </div>
                                        @endif
                                        @php
                                            $total = 0;
                                        @endphp
                                        @if ($hasNonBatchProducts)
                                            <div class="col-md-12">
                                                {{-- <h2 style="margin: 20px 0 0 15px">Xuất lẻ</h2> --}}
                                                <table class="table-order table-divided table-vcenter card-table col-12">
                                                    <tbody>
                                                        @foreach ($proposal->proposalHubIssueDetail as $orderProduct)
                                                            @if ($orderProduct->is_batch == 0)
                                                                <tr class="item__product">
                                                                    <td class="vertical-align-t" width="10%">
                                                                        @if ($orderProduct->id)
                                                                            <div class="wrap-img">
                                                                                <img style="margin-top: 20px"
                                                                                    class="thumb-image thumb-image-cartorderlist"
                                                                                    src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->parentProduct?->first()?->image,'thumb',false,RvMedia::getDefaultImage()) }}"
                                                                                    alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}"
                                                                                    width="100px" height="100px">
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                    <td class="pl5 p-r5" width="20%">
                                                                        {{ $orderProduct->product_name }}
                                                                        <div>
                                                                            Màu: {{ $orderProduct->color }} - Size:
                                                                            {{ $orderProduct->size }}

                                                                        </div>

                                                                        <input type="text" class="widget__price"
                                                                            name="product[{{ $orderProduct->product_id }}][size]"
                                                                            value="{{ $orderProduct->size }}" hidden>
                                                                        <input type="text" class="widget__price"
                                                                            name="product[{{ $orderProduct->product_id }}][color]"
                                                                            value="{{ $orderProduct->color }}" hidden>
                                                                    </td>
                                                                    <td class="pl5 p-r5" width="20%">
                                                                        <div class="inline_block">
                                                                            <span>SKU:
                                                                                <strong>{{ $orderProduct->sku }}</strong></span>
                                                                        </div>
                                                                    </td>
                                                                    @php
                                                                        $quantity = QuantityProductInStock::where([
                                                                            'product_id' => $orderProduct->product_id,
                                                                            'stock_id' => $proposal->warehouse_issue_id,
                                                                        ])?->first()?->quantity;
                                                                        $total += $orderProduct->quantity;
                                                                    @endphp
                                                                    <td class="pl5 p-r5" width="20%">
                                                                        Tồn kho : {{ $quantity }} sản phẩm
                                                                    </td>
                                                                    <td class="pl5 p-r5" width="15%">
                                                                        Đề xuất : {{ $orderProduct->quantity }} sản phẩm
                                                                        <input type="number"
                                                                            name="product[{{ $orderProduct->product_id }}][quantityStock]"
                                                                            value="{{ $quantity }}" hidden />
                                                                        <input type="number"
                                                                            name="product[{{ $orderProduct->product_id }}][quantity]"
                                                                            hidden
                                                                            class="form-control input_quantity text-center"
                                                                            value="{{ $orderProduct->quantity }}"
                                                                            min="1" max="{{ $quantity }}"
                                                                            placeholder="0" required>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                        <tr>
                                                            <td colspan="6" class="text-end h5">
                                                                <h3>
                                                                    <strong>Tổng số lượng đề xuất: </strong> <span
                                                                        class="check__highlight">{{ $total }} sản
                                                                        phẩm</span>
                                                                </h3>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
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
                                        <button class="btn btn-primary" id="submitForm"
                                            type="submit">{{ __('Duyệt đơn') }}</button>
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
                                            action="{{ route('proposal-hub-issue.denied', $proposal->id) }}">
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
        $("#submitForm").on("click", function() {
            $(this).prop('disabled', true);
            $("#denyButton").prop('disabled', true);
        });
        $("#denySubmit").on("click", function(e) {
            e.preventDefault();
            var denyReason = $("#denyReason").val();
            if (denyReason.trim() === "") {
                alert("Vui lòng nhập lý do từ chối.");
                return;
            }
            $(this).prop('disabled', true);
            $("#submitForm").prop('disabled', true);
            $("#denyButton").prop('disabled', true);
            $("#denyForm").submit();
        });
    </script>
@stop
<style>
    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-hover tbody tr:hover {
        color: #212529;
        background-color: rgba(0, 0, 0, .075);
    }
</style>
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
