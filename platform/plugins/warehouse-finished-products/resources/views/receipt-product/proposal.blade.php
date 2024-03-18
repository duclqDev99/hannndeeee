@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
        use Botble\WarehouseFinishedProducts\Models\ProductBatch;
        use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
        use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
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
    <div class="max-width-1200" id="main-order-content">
        <div class="row row-cards justify-content-center">
            <div class="col-lg-9 col-md-12 col-12">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <form action="{{ route('proposal-receipt-products.approved', $proposal) }}" method="post">
                                    @csrf
                                    <input type="text" name="proposal_code" value="{{ $proposal->proposal_code }}"
                                        hidden>

                                    <div class="wrapper-content">
                                        <div class="pd-all-20">
                                            <div class="card">
                                                <div class="card-header">
                                                    <div>
                                                        <h2 class="title">Thông tin đơn đề xuất nhập kho
                                                            {{ BaseHelper::clean(get_proposal_receipt_product_code($proposal->proposal_code)) }}
                                                        </h2>
                                                        <div>
                                                            <h3>Mục đích nhập kho {{ $proposal->title }}</h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info-group">
                                                                <label>Kho xuất:</label>
                                                                <strong>
                                                                    @if ($proposal->is_warehouse == 'warehouse-odd')
                                                                        {{ $proposal->wh_departure_name }}
                                                                    @else
                                                                        Nhập kho hàng tồn
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
                                                                <label>Kho nhận:</label>
                                                                <strong>{{ $proposal->warehouse_name }}</strong>
                                                            </div>
                                                            <div>
                                                                Người đề xuất:
                                                                <strong>{{ $proposal->invoice_issuer_name }}</strong>
                                                            </div>

                                                        </div>
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
                                                        @foreach ($proposal->proposalDetail as $product)
                                                            <tr class="item__product">
                                                                <td class="width-60-px min-width-60-px vertical-align-t">
                                                                    @if ($product->product_id)
                                                                        <div class="wrap-img">
                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                style="max-width: 100px;"
                                                                                src="{{ RvMedia::getImageUrl($product->product($product->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                alt="{{ $product->product($product->product_id)->first()?->name }}">
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                                <td class="p-3 min-width-200-px">
                                                                    {{ $product->product_name }}
                                                                    @if ($proposal->is_warehouse != 'warehouse')
                                                                        , màu: {{ $product->color }}, size:
                                                                        {{ $product->size }}
                                                                    @endif
                                                                </td>
                                                                <td class="p-3">
                                                                    <div class="inline_block">
                                                                        <input type="number" class="widget__price"
                                                                            name="product[{{ $product->id }}][product_price]"
                                                                            value="{{ $product->price }}" hidden>
                                                                        <span>Sku:
                                                                            <strong>{{ $product->sku }}</strong></span>
                                                                    </div>
                                                                </td>
                                                                @if ($proposal->is_warehouse == 'warehouse-odd')
                                                                    <td class="p-3">
                                                                        <div class="d-flex align-items-center">
                                                                            {{-- @if ($proposal->is_warehouse == 'warehouse')
                                                                            <span style="white-space: nowrap;">SL trong kho:
                                                                                {{ !empty($proposal->wh_departure_id)
                                                                                    ? ProductBatch::where([
                                                                                        'warehouse_type' => WarehouseFinishedProducts::class,
                                                                                        'warehouse_id' => $proposal->wh_departure_id,
                                                                                        'product_parent_id' => $product->product_id,
                                                                                        'status' => ProductBatchStatusEnum::INSTOCK,
                                                                                    ])->groupBy('product_parent_id')->count()
                                                                                    : '0' }}
                                                                                lô</span>
                                                                        @else --}}

                                                                            <span style="white-space: nowrap;">Tồn kho xuất:
                                                                                {{ QuantityProductInStock::where(['product_id' => $product->product_id, 'stock_id' => $proposal->wh_departure_id])->first()->quantity }}
                                                                                sản phẩm</span>
                                                                            {{-- @endif --}}
                                                                        </div>

                                                                    </td>
                                                                @endif
                                                                <td class="p-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <span style="white-space: nowrap;">Tồn kho nhận:
                                                                            {{ QuantityProductInStock::where(['product_id' => $product->product_id, 'stock_id' => $proposal->warehouse_id])?->first()?->quantity ?: 0 }}
                                                                            sản phẩm</span>

                                                                    </div>

                                                                </td>

                                                                <td class="p-3" width="200px">
                                                                    <div class="d-flex align-items-center">
                                                                        <span style="white-space: nowrap;">Đề xuất:
                                                                            {{ $product->quantity }}</span>
                                                                        <input type="number" class="value__total__price"
                                                                            value="{{ $product->price * $product->quantity }}"
                                                                            hidden>
                                                                        <input
                                                                            type="number"
                                                                            name="product[{{ $product->id }}][quantity]"
                                                                            class="form-control widget__quantity"
                                                                            value="{{ $product->quantity }}" min="1"
                                                                            placeholder="0" hidden>

                                                                        {{-- @if ($proposal->is_warehouse == 'warehouse')
                                                                            <input
                                                                                data-quantity="{{ !empty($proposal->wh_departure_id)
                                                                                    ? ProductBatch::where([
                                                                                        'warehouse_type' => WarehouseFinishedProducts::class,
                                                                                        'warehouse_id' => $proposal->wh_departure_id,
                                                                                        'product_parent_id' => $product->product_id,
                                                                                    ])->groupBy('product_parent_id')->count()
                                                                                    : '0' }}"
                                                                                type="number"
                                                                                name="product[{{ $product->id }}][quantity]"
                                                                                class="form-control widget__quantity"
                                                                                value="{{ $product->quantity }}"
                                                                                min="1" placeholder="0" required>
                                                                        @else
                                                                            <input
                                                                                data-quantity="{{ !empty($proposal->wh_departure_id) ? QuantityProductInStock::where(['product_id' => $product->product_id, 'stock_id' => $proposal->wh_departure_id])->first()->quantity : '0' }}"
                                                                                type="number"
                                                                                name="product[{{ $product->id }}][quantity]"
                                                                                class="form-control widget__quantity"
                                                                                value="{{ $product->quantity }}"
                                                                                min="1" placeholder="0" required>
                                                                        @endif --}}

                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $totalQty += $product->quantity;
                                                            @endphp
                                                        @endforeach
                                                        <tr>
                                                            <td colspan="5" class="text-end h5">
                                                                <input type="number" name="ac_amount"
                                                                    value="{{ $proposal->amount }}" class="ac_amount"
                                                                    hidden>
                                                                {{-- <strong>Tổng số lượng: </strong> <span
                                                                    class="widget__amount"></span> --}}
                                                                <strong>Tổng số lượng:</strong> <span
                                                                    class="widget__total_quantity"> {{$proposal->quantity}}</span>
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
                                                            placeholder="{{ __('Ghi chú') }}">{{ $proposal->description }}</textarea>
                                                    </div>
                                                </div>
                                                @php
                                                    $inputDate = $proposal->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                                    $formattedDate = date('d/m/Y', strtotime($inputDate));
                                                @endphp
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="row">
                                                        <div class="col-lg-6 col-sm-12">
                                                            <div class="py-3">
                                                                <label
                                                                    class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                                <div class="input-group datepicker">
                                                                    <input class="form-control flatpickr-input"
                                                                        data-date-format="d-m-Y"
                                                                        value="{{ $formattedDate }}" name="expected_date"
                                                                        id="expected_date">
                                                                    <button class="btn btn-icon" type="button"
                                                                        data-toggle="data-toggle">
                                                                        <span class="icon-tabler-wrapper icon-left">
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                class="icon icon-tabler icon-tabler-calendar"
                                                                                width="24" height="24"
                                                                                viewBox="0 0 24 24" stroke-width="2"
                                                                                stroke="currentColor" fill="none"
                                                                                stroke-linecap="round"
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
                                                                    <button class="btn btn-icon   text-danger"
                                                                        type="button" data-clear="data-clear">
                                                                        <span class="icon-tabler-wrapper icon-left">
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                class="icon icon-tabler icon-tabler-x"
                                                                                width="24" height="24"
                                                                                viewBox="0 0 24 24" stroke-width="2"
                                                                                stroke="currentColor" fill="none"
                                                                                stroke-linecap="round"
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
                                            @if ($proposal->status != \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::APPOROVED)
                                                <div class="mt10">
                                                    <a href="#" class="btn btn-danger" data-toggle="modal"
                                                        data-target=".bg-modal-cancel">Huỷ đơn</a>
                                                    <button class="btn btn-primary"
                                                        type="submit">{{ __('Duyệt đơn') }}</button>
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
        </div>
    </div>
    <div class="modal fade bg-modal-cancel" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true">
        <form action="{{ route('proposal-receipt-products.cancel', $proposal) }}" method="post">
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header px-3">
                        <h5 class="modal-title" id="exampleModalLongTitle">Xác nhận huỷ đơn</h5>
                        <button type="button" class="btn btn-secondary close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card-menu">
                            <div class="form-group">
                                <label for="">Lý do huỷ đơn:</label>
                                <textarea name="reasoon" class="form-control" placeholder="Ghi lý do huỷ đơn của bạn" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Xác nhận</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
<script>
    window.addEventListener('load', function() {
        const tableReceipt = document.querySelector('.table-order.table-divided');
        const itemProduct = tableReceipt?.querySelectorAll('.item__product');

        if (itemProduct) {
            const wg_tax = tableReceipt.querySelector('.widget__tax__amount')
            const wg_amount = tableReceipt.querySelector('.widget__amount')
            const widget__total_quantity = tableReceipt.querySelector('.widget__total_quantity')

            itemProduct.forEach(product => {
                const wg_price = product.querySelector('.widget__price')
                const wg_quantity = product.querySelector('.widget__quantity')

                // if (wg_price && wg_quantity) {
                //     getTotalAmount(tableReceipt)
                //     wg_quantity.addEventListener('keyup', function(event) {
                //         let curQty = event.target.value;

                //         let qtyInStock = event.target.dataset.quantity;

                //         if (qtyInStock * 1 > 0 && qtyInStock * 1 < curQty * 1) {
                //             alert('Số lượng nhập vượt quá số lượng trong kho!!');
                //             wg_quantity.value = qtyInStock;
                //         }

                //         getTotalAmount(tableReceipt)
                //     })

                //     wg_quantity.addEventListener('change', function(event) {
                //         let curQty = event.target.value;

                //         let qtyInStock = event.target.dataset.quantity;

                //         if (qtyInStock * 1 > 0 && qtyInStock * 1 < curQty * 1) {
                //             alert('Số lượng nhập vượt quá số lượng trong kho!!');
                //             wg_quantity.value = qtyInStock;
                //         }
                //         getTotalAmount(tableReceipt)
                //     })
                // }

            });
        }

        // Tạo Flatpickr
        var flatpickrInput = flatpickr('.flatpickr-input', {
            dateFormat: 'd-m-Y',
            minDate: "today",
        });
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
            const wg_totalPrice = table.querySelector('.widget__total_quantity')
            let amount = 0;

            itemProduct.forEach(product => {
                const wg_quantity = product.querySelector('.widget__quantity');
                amount += wg_quantity.value * 1;
            });

            wg_totalPrice.textContent = amount
        }
    }

    function closeCalendar(flatpickrInstance) {
        // Đóng calendar bằng cách gọi close()
        flatpickrInstance.close();
    }
</script>
