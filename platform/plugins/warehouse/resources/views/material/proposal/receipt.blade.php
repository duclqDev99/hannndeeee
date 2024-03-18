@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
        use Botble\Warehouse\Models\QuantityMaterialStock;
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
            <div class="col-md-9">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <form action="{{ route('material-receipt-confirm.receipt', $proposal->id) }}" method="post">
                                    @csrf
                                    <input type="text" name="proposal_code" value="{{ $proposal->proposal_code }}"
                                        hidden>
                                    <div class="wrapper-content">
                                        <div class="pd-all-20">
                                            <div class="card-header justify-content-between">
                                                <div class="flexbox-auto-right mr5">
                                                    <label
                                                        $class="title-product-main text-no-bold">{{ __('Phiếu đề xuất nhập kho') }}
                                                        <strong>{{ $proposal->proposal_code }}</strong> -
                                                        @if ($proposal->is_from_supplier === 0)
                                                            Nhập từ: <strong>{{ $proposal->wh_departure_name }} <i
                                                                    class="fa-solid fa-arrow-right"></i>
                                                                {{ $proposal->warehouse_name }}</strong>
                                                        @else
                                                            {{ $proposal->warehouse_name }}
                                                        @endif
                                                    </label>
                                                    <div>
                                                        Người đề xuất: <strong>{{ $proposal->invoice_issuer_name }}</strong> - Vào ngày: {{ date('d/m/Y', strtotime($proposal->created_at)) }}
                                                    </div>
                                                    <div>
                                                        Tiêu đề: {{ $proposal->title }}
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="mt20">
                                                @if ($proposal->stat)
                                                    <svg class="svg-next-icon svg-next-icon-size-16 next-icon--right-spacing-quartered text-info"
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                        enable-background="new 0 0 24 24">
                                                        <g>
                                                            <path
                                                                d="M20.2 1H3.9C2.3 1 1 2.3 1 3.9v16.9C1 22 2.1 23 3.4 23h17.3c1.3 0 2.3-1 2.3-2.3V3.9C23 2.3 21.8 1 20.2 1zM20 4v11h-2.2c-1.3 0-2.8 1.5-2.8 2.8v1c0 .3.2.2-.1.2H8.2c-.3 0-.2.1-.2-.2v-1C8 16.5 6.7 15 5.3 15H4V4h16zM10.8 14.7c.2.2.6.2.8 0l7.1-6.9c.3-.3.3-.6 0-.8l-.8-.8c-.2-.2-.6-.2-.8 0l-5.9 5.7-2.4-2.3c-.2-.2-.6-.2-.8 0l-.8.8c-.2.2-.2.6 0 .8l3.6 3.5z">
                                                            </path>
                                                        </g>
                                                    </svg>
                                                    <strong
                                                        class="ml5 text-info">{{ trans('plugins/ecommerce::order.completed') }}</strong>
                                                @else
                                                    <svg class="svg-next-icon svg-next-icon-size-16 text-warning"
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                                        enable-background="new 0 0 16 16">
                                                        <g>
                                                            <path
                                                                d="M13.9130435,0 L2.08695652,0 C0.936347826,0 0,0.936347826 0,2.08695652 L0,14.2608696 C0,15.2194783 0.780521739,16 1.73913043,16 L14.2608696,16 C15.2194783,16 16,15.2194783 16,14.2608696 L16,2.08695652 C16,0.936347826 15.0636522,0 13.9130435,0 L13.9130435,0 Z M13.9130435,2.08695652 L13.9130435,10.4347826 L12.173913,10.4347826 C11.2153043,10.4347826 10.4347826,11.2153043 10.4347826,12.173913 L10.4347826,12.8695652 C10.4347826,13.0615652 10.2789565,13.2173913 10.0869565,13.2173913 L5.2173913,13.2173913 C5.0253913,13.2173913 4.86956522,13.0615652 4.86956522,12.8695652 L4.86956522,12.173913 C4.86956522,11.2153043 4.08904348,10.4347826 3.13043478,10.4347826 L2.08695652,10.4347826 L2.08695652,2.08695652 L13.9130435,2.08695652 L13.9130435,2.08695652 Z">
                                                            </path>
                                                        </g>
                                                    </svg>
                                                    <strong
                                                        class="ml5 text-warning">{{ trans('plugins/ecommerce::order.uncompleted') }}</strong>
                                                @endif
                                            </div> --}}
                                        </div>
                                        <div class="pd-all-20 p-none-t border-top-title-main">
                                            <div class="table-wrap">
                                                <table class="table-order table-divided table-vcenter card-table w-100">
                                                    <tbody>
                                                        @php
                                                            $totalQty = 0;
                                                        @endphp
                                                        @foreach ($proposal->proposalDetail as $orderMaterial)
                                                            <tr class="item__product">
                                                                <td class="width-60-px min-width-60-px vertical-align-t">
                                                                    @if ($orderMaterial->material_id)
                                                                        <div class="wrap-img">
                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                style="max-width: 100px;"
                                                                                src="{{ RvMedia::getImageUrl($orderMaterial->material($orderMaterial->material_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                alt="{{ $orderMaterial->material($orderMaterial->material_id)->first()?->name }}">
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                                <td class="p-3 min-width-200-px">
                                                                    {{ $orderMaterial->material_name }}
                                                                </td>
                                                                <td class="p-3">
                                                                    <div class="inline_block">
                                                                        <input type="number" class="widget__price"
                                                                            name="material[{{ $orderMaterial->id }}][material_price]"
                                                                            value="{{ $orderMaterial->material_price }}"
                                                                            hidden>
                                                                        <span>Mã nguyên liệu:
                                                                            <strong>{{ $orderMaterial->material_code }}</strong></span>
                                                                    </div>
                                                                </td>
                                                                @if ($proposal->is_from_supplier === 1)
                                                                    <td class="p-3" width="200px">
                                                                        <div class="d-flex align-items-center">
                                                                            {{ $orderMaterial->supplier_name }}
                                                                        </div>
                                                                    </td>
                                                                @endif
                                                                <td class="p-3" width="200px">
                                                                    <div class="d-flex align-items-center">
                                                                        <span style="white-space: nowrap;">Số lượng:</span>
                                                                        <input data-quantity="{{ !empty($proposal->wh_departure_id) ? QuantityMaterialStock::where(['material_id' => $orderMaterial->material_id, 'warehouse_id' => $proposal->wh_departure_id])->first()->quantity : '0' }}" type="number" name="material[{{$orderMaterial->id }}][quantity]" class="form-control widget__quantity" value="{{ $orderMaterial->material_quantity }}" min="1" placeholder="0" required>
                                                                    </div>
                                                                </td>
                                                                <td class="p-3 text-end">
                                                                    <input type="number" class="value__total__price"
                                                                        value="{{ $orderMaterial->material_price * $orderMaterial->material_quantity }}"
                                                                        hidden>
                                                                    <span class="widget__total__price">Đơn vị:
                                                                        {{ $orderMaterial->material_unit }}</span>
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $totalQty += $orderMaterial->material_quantity;
                                                            @endphp
                                                        @endforeach
                                                        <tr>
                                                            <td colspan="6" class="text-end h5">
                                                                <input type="number" name="ac_amount"
                                                                    value="{{ $proposal->amount }}" class="ac_amount"
                                                                    hidden>
                                                                {{-- <strong>Tổng số lượng: </strong> <span
                                                                    class="widget__amount"></span> --}}
                                                                <strong>Tổng số lượng: </strong> <span
                                                                    class="widget__total_quantity"></span>
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
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="row">
                                                        <div class="col-lg-6 col-sm-12">
                                                            <div class="py-3">
                                                                <label
                                                                    class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                                @php
                                                                    $inputDate = $proposal->expected_date; // Giả sử $proposal->expected_date là ngày được truyền từ Controller
                                                                    $formattedDate = date('d/m/Y', strtotime($inputDate));
                                                                @endphp
                                                                <div class="input-group datepicker">
                                                                    <input class="form-control flatpickr-input"
                                                                        value="{{$formattedDate}}"
                                                                        name="expected_date"
                                                                        id="expected_date"
                                                                        aria-invalid="false"
                                                                        aria-describedby="expected_date-error">
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
                                                                    <button class="btn btn-icon text-danger"
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
                                            @if ($proposal->status != MaterialProposalStatusEnum::APPOROVED)
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
        <form action="{{ route('material-proposal-purchase.cancel', $proposal->id) }}" method="post">
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

                if (wg_price && wg_quantity) {
                    getTotalAmount(tableReceipt)
                    wg_quantity.addEventListener('keyup', function(event) {
                        let curQty = event.target.value;

                        let qtyInStock = event.target.dataset.quantity;

                        if (qtyInStock * 1 > 0 && qtyInStock * 1 < curQty * 1) {
                            alert('Số lượng nhập vượt quá số lượng trong kho!!');
                            wg_quantity.value = qtyInStock;
                        }

                        getTotalAmount(tableReceipt)
                    })

                    wg_quantity.addEventListener('change', function(event) {
                        let curQty = event.target.value;

                        let qtyInStock = event.target.dataset.quantity;

                        if (qtyInStock * 1 > 0 && qtyInStock * 1 < curQty * 1) {
                            alert('Số lượng nhập vượt quá số lượng trong kho!!');
                            wg_quantity.value = qtyInStock;
                        }
                        getTotalAmount(tableReceipt)
                    })
                }

            });
        }

        // Tạo Flatpickr
        var flatpickrInput = flatpickr('.flatpickr-input', {
            dateFormat: 'd-m-Y',
            minDate: 'today'
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
</script>
