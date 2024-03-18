@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\Warehouse\Enums\BaseStatusEnum;
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

        .scanner {
            width: 100%;
            height: 2px;
            background-color: red;
            opacity: 0.7;
            position: absolute;
            box-shadow: 0px 0px 2px 4px rgba(170, 11, 23, 0.49);
            top: 50%;
            animation-name: scan;
            animation-duration: 1.5s;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
            animation-play-state: paused;
            animation-play-state: running;

        }

        @keyframes scan {
            0% {
                box-shadow: 0px 0px 2px 6px rgba(170, 11, 23, 0.49);
                top: 50%;
            }

            25% {
                box-shadow: 0px 0px 2px 6px rgba(170, 11, 23, 0.49);
                top: 10%;
            }

            75% {
                box-shadow: 0px 0px 2px 6px rgba(170, 11, 23, 0.49);
                top: 90%;
            }
        }
    </style>

    <div class="modal fade modal-blur" id="QrScanReceiveModal" tabindex="-1" data-select2-dropdown-parent="true"
        aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quét QR kiểm kê sản phẩm nhập kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col overflow-auto" id="table_wrapper" style="max-height: 460px">
                            <div class="card border-0 position-relative mb-3">
                                <div class="card-header border border-bottom-0">
                                    <h4 class="card-title fw-bolder">Thông tin sản phẩm nhập kho</h4>
                                </div>
                                <div class="card-body p-0 ">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">STT</th>
                                                <th scope="col">Thông tin sản phẩm</th>
                                                <th scope="col">Số lượng nhập dự kiến</th>
                                                <th scope="col">Số lượng QR hợp lệ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="product_table_list"> </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card border-0 position-relative">
                                <div class="card-header border border-bottom-0">
                                    <h4 class="card-title fw-bolder">Lịch sử quét</h4>
                                </div>
                                <div class="card-body p-0 ">
                                    <table class="mb-0 table table-bordered ">
                                        <thead>
                                            <tr>
                                                <th scope="col">STT</th>
                                                <th scope="col">QR Code</th>
                                                <th scope="col">Lượt xuất file</th>
                                                <th scope="col">Tên sản phẩm</th>
                                                <th scope="col">trạng thái</th>
                                                <th scope="col">Thông tin kho</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table_list"> </tbody>
                                    </table>
                                    <div id="empty_scanned_message"
                                        class="flex-grow-1 border border-top-0 d-flex align-items-center justify-content-center">
                                        <p class="my-3">Chưa có thông tin QR được quét!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Qr Camera --}}
                        <div class="col-3 d-flex flex-column">
                            <div class="card ">
                                <div class="card-body p-2 ">
                                    <div id="scanner_box"
                                        class="position-relative w-100 bg-light d-flex gap-3 flex-column align-items-center justify-content-center overflow-hidden"
                                        style="height: 270px; border-radius: 5px">
                                        {{-- <div class='scanner'></div> --}}
                                        <div class="w-25">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z" />
                                                <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z" />
                                                <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z" />
                                                <path
                                                    d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z" />
                                                <path d="M12 9h2V8h-2z" />
                                            </svg>
                                        </div>
                                        <p class="">Đặt mã QR vào tia máy quét</p>
                                    </div>

                                    <div id="scanner_box_loading"
                                        class="position-relative w-100 d-none gap-3 flex-column align-items-center justify-content-center overflow-hidden"
                                        style="height: 270px; border-radius: 5px; background-color: #307ff1">
                                        <div class='scanner'></div>
                                        <div class="w-25 text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z" />
                                                <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z" />
                                                <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z" />
                                                <path
                                                    d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z" />
                                                <path d="M12 9h2V8h-2z" />
                                            </svg>
                                        </div>
                                        <p class="text-white">Đang kiểm tra mã QR...</p>
                                    </div>

                                </div>
                            </div>
                            <div id="scanner_message" style="display: none"> </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="save_qr_scan_btn" disabled class="btn btn-primary" type="button"
                        id="store-related-attributes-button">
                        Lưu
                    </button>
                    <button disabled class="btn" type="button" id="reset_btn">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-blur" id="confirmModal" tabindex="-1" role="dialog"
        data-select2-dropdown-parent="true" aria-modal="true" >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-status bg-danger"></div>
                <div class="modal-body text-center py-4">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="mb-2">
                        <span class="icon-tabler-wrapper icon-lg text-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M12 9v4"></path>
                                <path
                                    d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                </path>
                                <path d="M12 16h.01"></path>
                            </svg>


                        </span>
                    </div>

                    <h3>Reset form?</h3>

                    <div class="text-muted text-break">
                        Tất cả dữ liệu vừa quét sẽ bị mất. Vui lòng cân nhắc!
                    </div>
                </div>

                <div class="modal-footer bg-white">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="w-100 btn btn-danger" id="confirm_reset_btn">
                                    Đồng ý
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="w-100 btn btn-" data-bs-dismiss="modal">
                                    Hủy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="widget__view row row-cards justify-content-center">
        <div class="col-12 ">
            <div class="d-flex justify-content-end">
                <a type="button" class="btn btn-primary d-inline" data-bs-toggle="modal" href="#QrScanReceiveModal"
                    id="open_scan_modal">
                    <span class="me-2">
                        <i class="fa-solid fa-qrcode"></i>
                    </span>
                    Quét QR
                </a>
            </div>
        </div>
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
                                                lúc {{ date('d/m/Y', strtotime($receipt->created_at)) }}
                                            </label>
                                            <div>
                                                Kho: <strong>{{ $receipt->warehouse_name }}</strong>
                                                Loại nhập kho:
                                                    @if ($receipt->is_warehouse == 'warehouse')
                                                        Nhập từ kho {{ $receipt->wh_departure_name }}
                                                    @elseif ($receipt->is_warehouse == 'processing')
                                                        Nhập từ nhà cung cấp {{ $receipt->receiptDetail->first()->processing_house_name }}
                                                    @else
                                                            Nhập kho hàng tồn
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
                                                @push('footer')
                                                    <script>
                                                        window.product_infos = {!! json_encode($receipt->receiptDetail) !!};
                                                    </script>
                                                @endpush
                                                @php
                                                    $totalQty = 0;
                                                @endphp
                                                @foreach ($receipt->receiptDetail as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            @if ($orderProduct->product_id !== 0)
                                                                <div class="wrap-img">
                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                        style="max-width: 100px;"
                                                                        src="{{ RvMedia::getImageUrl($orderProduct->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $orderProduct->product->name }}">
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 min-width-200-px">
                                                            {{ $orderProduct->product_name }}
                                                            <br>
                                                            <span>Sku: <strong>{{ $orderProduct->sku }}</strong></span>
                                                            <input type="number" class="widget__price"
                                                                name="product[{{ $orderProduct->id }}][product_price]"
                                                                value="{{ $orderProduct->product_price }}" hidden>
                                                        </td>
                                                        @if ($receipt->is_warehouse === 0)
                                                            <td class="p-3">
                                                                <div class="d-flex align-items-center">
                                                                    {{ $orderProduct->processing_house_name }}
                                                                </div>
                                                            </td>
                                                        @endif
                                                        <td class="p-3 text-end">
                                                            <input type="number"
                                                                name="product[{{ $orderProduct->id }}][quantity]"
                                                                class="form-control widget__quantity"
                                                                value="{{ $orderProduct->quantity }}" min="0"
                                                                placeholder="0" hidden>
                                                            <span>Số lượng: {{ $orderProduct->quantity }}</span>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $totalQty += $orderProduct->quantity;
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

                            <form action="{{ route('receipt-product.approved', $receipt) }}" method="post">
                                @csrf
                                <div id="qr_ids_wrapper"></div>
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
                                                    @foreach ($receipt->receiptDetail as $orderProduct)
                                                        <tr class="item__product">
                                                            <td class="width-60-px min-width-60-px vertical-align-t">
                                                                @if ($orderProduct->product_id !== 0)
                                                                    <div class="wrap-img">
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            style="max-width: 100px;"
                                                                            src="{{ RvMedia::getImageUrl($orderProduct->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            alt="{{ $orderProduct->product->name }}">
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="p-3 min-width-200-px">
                                                                {{ $orderProduct->product_name }}
                                                                <br>
                                                                <span>Sku:
                                                                    <strong>{{ $orderProduct->sku }}</strong></span>
                                                            </td>
                                                            @if ($receipt->is_warehouse === 0)
                                                                <td class="p-3">
                                                                    <div class="d-flex align-items-center">
                                                                        {{ $orderProduct->processing_house_name }}
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            <td class="p-3 text-end" width="200px">
                                                                <div class="d-flex align-items-center">
                                                                    <span style="white-space: nowrap;">Số lượng: </span>

                                                                    <input type="number"
                                                                        name="product[{{ $orderProduct->id }}][quantity_default]"
                                                                        value="{{ $orderProduct->quantity }}" hidden>
                                                                    <input type="number"
                                                                        data-id="{{ $orderProduct->product_id }}"
                                                                        data-default="{{ $orderProduct->quantity }}"
                                                                        class="form-control base__quantity"
                                                                        name="product[{{ $orderProduct->id }}][quantity]"
                                                                        min="0"
                                                                        value="{{ $orderProduct->quantity }}">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="reasoon__receipt" style="display: none;">
                                                            <td class="pb-3">
                                                                <label>Lý do: </label>
                                                            </td>
                                                            <td colspan="3" class="pb-3">
                                                                <textarea class="form-control" name="product[{{ $orderProduct->id }}][reasoon]" rows="1"
                                                                    placeholder="Lí do thay đổi số lượng so với ban đầu"></textarea>
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $totalQty += $orderProduct->quantity;
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
                                    @if ($receipt->status != BaseStatusEnum::PUBLISHED)
                                        <div class="card-body pd-all-20 p-none-t">
                                            <div class="mt10">
                                                <button class="btn btn-primary" id='submit_btn' style="display: none"
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
                    const textarea = reasoon__receipt[index].querySelector('textarea');

                    reasoon__receipt[index].style.display = 'none';

                    let qty_default = inputQuantity.dataset.default;

                    inputQuantity?.addEventListener('keyup', function(event) {
                        event.preventDefault();

                        if (event.target.value == qty_default) {
                            reasoon__receipt[index].style.display = 'none';
                            textarea.removeAttribute('required');
                        } else {
                            reasoon__receipt[index].style.display = 'table-row';
                            textarea.setAttribute('required', true);
                        }
                    })

                    inputQuantity?.addEventListener('change', function(event) {
                        event.preventDefault();
                        console.log('change')
                        if (event.target.value == qty_default) {
                            reasoon__receipt[index].style.display = 'none';
                            textarea.removeAttribute('required');
                            console.log('require false');
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
