@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\Warehouse\Enums\BaseStatusEnum;
    @endphp
    <style>
        ul {
            list-style: none;
        }

        ul#wrap-widget-1 .widget-content {
            display: none
        }

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

        .sidebar-item {
            position: relative;
        }

        .btn-remove-batch {
            position: absolute;
            top: 0;
            right: 0;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-content: center;
            border-radius: 50%;
            transform: translate(-10px, 5px) scale(1.3);
            color: #B80000;
            cursor: pointer;
            z-index: 2;
            transition: all .3s ease-in-out;
        }

        .btn-remove-batch:hover {
            background: #B80000;
            color: #fff;
        }

        .btn-remove-batch i {
            line-height: unset;
        }

        .batch__list .sidebar-item:first-child .btn-remove-batch {
            display: none;
        }

        .ui-layout__item .wrapper-content {
            max-height: 800px;
            overflow-y: scroll;
        }

        .widget-item.filtered {
            cursor: not-allowed !important;
        }

        .widget-item.filtered .card {
            background: #ccc;
        }

        .widget_batch_warehouse {
            border: 1px solid #ccc;
            border-radius: 2px;
            padding: 5px;
        }

        .widget_batch_warehouse.filtered .card {
            cursor: not-allowed !important;
        }

        li>.widget_batch_warehouse.filtered {
            cursor: not-allowed !important;
        }
    </style>



    <div class="widget__view widget-main row row-cards justify-content-center" id="wrap-widgets">

        <div class="col-lg-6 col-md-12">
            <input type="text" id="url_app" value="{{ env('APP_URL') }}" hidden>
            <input type="text" id="api_key" value="{{ env('API_KEY') }}" hidden>
            <input type="number" id="receipt_id" value="{{ $receipt->id }}" hidden>
            <input type="number" id="current_user_id" value="{{ Auth::user()->id }}" hidden>
            <input type="text" id="type_receipt_product" value="{{$receipt->from_product_issue}}" hidden>
            <input type="text" id="url_api_batch_created" value="get-created-shipment" hidden>
            <input type="text" id="url_api_qrcode_for_batch" value="print-qrcode-for-batch" hidden>
            <div class="ui-layout">
                <div class="flexbox-layout-sections">
                    <div class="flexbox-layout-section-primary mt20">
                        <div class="card ui-layout__item">
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="card-header flexbox-grid-default">
                                        <div class="flexbox-auto-right mr5">
                                            <label class="h4 title-product-main text-no-bold">{{ __('Phiếu nhập kho') }}
                                                <strong>{{ !empty($receipt->proposal) ? get_proposal_receipt_product_code($receipt->proposal->proposal_code) : '' }}</strong>
                                                - Người đề xuất:
                                                <strong>{{ $receipt->invoice_issuer_name }},</strong>
                                                lúc {{ date('d/m/Y', strtotime($receipt->created_at)) }}
                                            </label>
                                            <div>
                                                @if ($receipt->is_warehouse === 1)
                                                    Nhập từ: <strong>{{ $receipt->wh_departure_name }} <i
                                                            class="fa-solid fa-arrow-right"></i>
                                                        {{ $receipt->warehouse_name }}</strong>
                                                @else
                                                    Kho: <strong>{{ $receipt->warehouse_name }}</strong>
                                                @endif
                                                - Loại nhập kho:
                                                @if ($receipt->is_warehouse == 'warehouse')
                                                    Nhập từ kho
                                                @elseif ($receipt->is_warehouse == 'processing')
                                                    Nhập từ nhà cung cấp
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

                                </div>
                                <div class="card-body pd-all-20 p-none-t">
                                    <div class="row">
                                        <div class="col-12">
                                            @php
                                                $products = [];
                                                $productBatchs = [];
                                                $fromWarehouse = false;

                                                $checkScanQR = true;
                                            @endphp
                                            <ul class="row row-cols-1 g-2 pl-0" id="wrap-widget-1" data-batch="1">
                                                @foreach ($receipt->receiptDetail as $product)
                                                    @if (!empty($product->batch_id))
                                                        @php
                                                            $batch = $product->batch->productInBatch->groupBy('product_id');
                                                            // dd($product->batch);
                                                        @endphp
                                                            <!--Kiểm tra nếu là nhập từ kho tới kho thì check theo lô để nhập kho-->
                                                        @if(!empty($batch))
                                                            @php
                                                                $fromWarehouse = true;
                                                                $productBatchs[] = $product->batch;
                                                            @endphp
                                                                <li
                                                                    data-batch="{{ $product->batch_id }}"
                                                                    class="col mb-3 widget-item {{$product->batch->status == \Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum::INSTOCK ? 'filtered' : $checkScanQR = false}}
                                                                    ">
                                                                    <div class="widget_batch_warehouse filtered"
                                                                        data-batch="{{ $product->batch_id }}">
                                                                        <div class="title">
                                                                            Lô:
                                                                            <strong>{{ $product->batch->batch_code }}</strong>
                                                                        </div>
                                                                        @foreach ($batch as $proId => $productBatch)
                                                                            <div class="widget_batch_warehouse_item">
                                                                                <input
                                                                                    name="batch[1][product][{{ $product->product_id }}][id]"
                                                                                    type="hidden"
                                                                                    value="{{ $product->product_id }}"
                                                                                    class="slt_product_id">
                                                                                <input type="number"
                                                                                    name="batch[1][parent_id]"
                                                                                    value="{{ $product->product->is_variation === 0 ? $product->product->id : $product->product->parentProduct->first()?->id }}"
                                                                                    class="slt_parent_id" hidden>
                                                                                <input type="number" class="start_quantity"
                                                                                    data-quantity="{{ $product->quantity }}"
                                                                                    hidden>
                                                                                <input type="number"
                                                                                    class="current_quantity"
                                                                                    data-quantity="{{ $product->quantity }}"
                                                                                    hidden>
                                                                                <input type="text" class="product_sku"
                                                                                    data-sku="{{ $product->sku }}" hidden>
                                                                                <input type="text" class="product_color"
                                                                                    data-color="{{ $product->color }}"
                                                                                    hidden>
                                                                                <input type="text" class="product_size"
                                                                                    data-size="{{ $product->size }}"
                                                                                    hidden>
                                                                                <input type="text" class="product_name"
                                                                                    data-name="{{ $product->product_name }}"
                                                                                    hidden>
                                                                                <x-core::card>
                                                                                    <x-core::card.header
                                                                                        class="d-flex justify-content-between p-3">
                                                                                        Tên: {{ $product->product_name }},
                                                                                        {{ $productBatch->first()?->product?->variationProductAttributes[0]?->title ?? '' }},
                                                                                        {{ $productBatch->first()?->product?->variationProductAttributes[1]?->title ?? '' }} <i
                                                                                            class="fa-solid fa-minus"></i> Mã:
                                                                                        {{ $product->sku }} <i class="fa-solid fa-minus"></i>
                                                                                        Số lượng: {{ $product->quantity }}

                                                                                        <x-core::card.header.button
                                                                                            class="d-none">
                                                                                            <x-core::icon size="sm"
                                                                                                name="ti ti-chevron-down" />
                                                                                        </x-core::card.header.button>
                                                                                    </x-core::card.header>
                                                                                </x-core::card>
                                                                                <x-core::form.fieldset
                                                                                    class="widget-content">
                                                                                    <div class="row">
                                                                                        <div class="col-lg-4 col-md-12">
                                                                                            <div class="form-group">
                                                                                                <label for="">Nhập
                                                                                                    số lượng sản
                                                                                                    phẩm:</label>
                                                                                                <input type="number"
                                                                                                    name="batch[1][product][{{ $product->product_id }}][quantity]"
                                                                                                    class="slt_quantity form-control"
                                                                                                    min="0" required>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-8 col-md-12">
                                                                                            {{-- <div class="form-group">
                                                                                                <label for="">Lý do:</label>
                                                                                                <input type="text"
                                                                                                    name="batch[1][product][{{ $product->product_id }}][reasoon]"
                                                                                                    class="slt_reasoon form-control"
                                                                                                    placeholder="Nhập lý do">
                                                                                            </div> --}}
                                                                                        </div>
                                                                                        <div class="col-lg-12 col-md-12">
                                                                                            <div class="form-group">
                                                                                                <label for="">Ghi
                                                                                                    chú:</label>
                                                                                                <textarea name="batch[1][product][{{ $product->product_id }}][description]" class="slt_note form-control"
                                                                                                    placeholder="Ghi chú"></textarea>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div
                                                                                        class="widget-control-actions mt-3 d-flex justify-content-end">
                                                                                        <x-core::button type="button"
                                                                                            :outlined="true"
                                                                                            class="widget-control-delete">
                                                                                            {{ trans('packages/widget::widget.delete') }}
                                                                                        </x-core::button>
                                                                                    </div>
                                                                                </x-core::form.fieldset>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </li>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                @if($checkScanQR === false)
                                                <div class="group-btn {{$fromWarehouse == true ? 'd-flex' : 'd-none'}} justify-content-end mt-2">
                                                    <a type="button" class="btn btn-primary btn_scan_qrcode d-inline" data-bs-toggle="modal" href="#scanBatchModal"
                                                        id="open_scan_modal_batch_warehouse" data-batch="" data-direction="left">
                                                        <span class="me-2">
                                                            <i class="fa-solid fa-qrcode"></i>
                                                        </span>
                                                        Quét QR
                                                    </a>
                                                </div>
                                                @endif
                                                @foreach ($receipt->receiptDetail as $product)
                                                    @if (empty($product->batch_id) && empty($product->is_odd))
                                                        @php
                                                            $products[] = $product;
                                                        @endphp
                                                        <li data-id="{{ $product->product_id }}"
                                                            class="col mb-3 widget-item">
                                                            <div class="widget_batch_warehouse_item">
                                                                <input
                                                                    name="batch[1][product][{{ $product->product_id }}][id]"
                                                                    type="hidden" value="{{ $product->product_id }}"
                                                                    class="slt_product_id">
                                                                <input type="number" name="batch[1][parent_id]"
                                                                    value="{{ $product->product($product->product_id)->first()?->parentProduct->first()->id }}"
                                                                    class="slt_parent_id" hidden>
                                                                <input type="number" class="start_quantity"
                                                                    data-quantity="{{ $product->quantity }}" hidden>
                                                                <input type="number" class="current_quantity"
                                                                    data-quantity="{{ $product->quantity }}" hidden>
                                                                <input type="text" class="product_sku"
                                                                    data-sku="{{ $product->sku }}" hidden>
                                                                <input type="text" class="product_color"
                                                                    data-color="{{ $product->color }}" hidden>
                                                                <input type="text" class="product_size"
                                                                    data-size="{{ $product->size }}" hidden>
                                                                <input type="text" class="product_name"
                                                                    data-name="{{ $product->product_name }}" hidden>
                                                                <x-core::card>
                                                                    <x-core::card.header
                                                                        class="d-flex justify-content-between p-3">
                                                                        Tên: {{ $product->product_name }}, size:
                                                                        {{ $product->size ?: '...' }}, color:
                                                                        {{ $product->color ?: '...' }} <i
                                                                            class="fa-solid fa-minus"></i> Mã:
                                                                        {{ $product->sku }} <i
                                                                            class="fa-solid fa-minus"></i>
                                                                        Số lượng gốc: {{ $product->quantity }}

                                                                        <x-core::card.header.button class="d-none">
                                                                            <x-core::icon size="sm"
                                                                                name="ti ti-chevron-down" />
                                                                        </x-core::card.header.button>
                                                                    </x-core::card.header>
                                                                </x-core::card>
                                                                <x-core::form.fieldset class="widget-content">
                                                                    <div class="row">
                                                                        <div class="col-lg-4 col-md-12">
                                                                            <div class="form-group">
                                                                                <label for="">Nhập số lượng sản
                                                                                    phẩm:</label>
                                                                                <input type="number"
                                                                                    name="batch[1][product][{{ $product->product_id }}][quantity]"
                                                                                    class="slt_quantity form-control"
                                                                                    min="0" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12 col-md-12">
                                                                            <div class="form-group">
                                                                                <label for="">Ghi chú:</label>
                                                                                <textarea name="batch[1][product][{{ $product->product_id }}][description]" class="slt_note form-control"
                                                                                    placeholder="Ghi chú"></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div
                                                                        class="widget-control-actions mt-3 d-flex justify-content-end">
                                                                        <x-core::button type="button" :outlined="true"
                                                                            class="widget-control-delete">
                                                                            {{ trans('packages/widget::widget.delete') }}
                                                                        </x-core::button>
                                                                    </div>
                                                                </x-core::form.fieldset>
                                                                <div class="widget-description mt-1">
                                                                    <x-core::form.helper-text>
                                                                        Số lượng còn lại: <strong
                                                                            class="content-current-quantity">{{ $product->quantity }}</strong>
                                                                    </x-core::form.helper-text>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endif
                                                @endforeach

                                                @php
                                                    $productOdd = $receipt->receiptDetail()->where(['batch_id' => null, 'is_odd' => 1])->get()->groupBy('product_id');
                                                    $arrQRcodeIdOdd = $receipt->receiptDetail()->where(['batch_id' => null, 'is_odd' => 1])->pluck('qrcode_id')->all();
                                                @endphp
                                                @foreach ($productOdd as $pro_odd_id => $productGroup)
                                                    @php
                                                        $product = $productGroup->first();
                                                        $product['qr_ids'] = $arrQRcodeIdOdd;
                                                        $products[] = $product;
                                                    @endphp
                                                    <li data-id="{{ $pro_odd_id }}" class="col mb-3 widget-item">
                                                        <div class="widget_batch_warehouse_item">
                                                            <input name="batch[1][product][{{ $pro_odd_id }}][id]"
                                                                type="hidden" value="{{ $pro_odd_id }}"
                                                                class="slt_product_id">
                                                            <input type="number" name="batch[1][parent_id]"
                                                                value="{{ $product->product($pro_odd_id)->first()?->parentProduct->first()->id }}"
                                                                class="slt_parent_id" hidden>
                                                            <input type="number" class="start_quantity"
                                                                data-quantity="{{ count($productGroup) }}" hidden>
                                                            <input type="number" class="current_quantity"
                                                                data-quantity="{{ count($productGroup) }}" hidden>
                                                            <input type="text" class="product_sku"
                                                                data-sku="{{ $product->sku }}" hidden>
                                                            <input type="text" class="product_color"
                                                                data-color="{{ $product->color }}" hidden>
                                                            <input type="text" class="product_size"
                                                                data-size="{{ $product->size }}" hidden>
                                                            <input type="text" class="product_name"
                                                                data-name="{{ $product->product_name }}" hidden>
                                                            <x-core::card>
                                                                <x-core::card.header
                                                                    class="d-flex justify-content-between p-3">
                                                                    Tên: {{ $product->product_name }}, size:
                                                                    {{ $product->size ?: '...' }}, color:
                                                                    {{ $product->color ?: '...' }} <i
                                                                        class="fa-solid fa-minus"></i> Mã:
                                                                    {{ $product->sku }} <i class="fa-solid fa-minus"></i>
                                                                    Số lượng gốc: {{ count($productGroup) }}

                                                                    <x-core::card.header.button class="d-none">
                                                                        <x-core::icon size="sm"
                                                                            name="ti ti-chevron-down" />
                                                                    </x-core::card.header.button>
                                                                </x-core::card.header>
                                                            </x-core::card>
                                                            <x-core::form.fieldset class="widget-content">
                                                                <div class="row">
                                                                    <div class="col-lg-4 col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="">Nhập số lượng sản
                                                                                phẩm:</label>
                                                                            <input type="number"
                                                                                name="batch[1][product][{{ $pro_odd_id }}][quantity]"
                                                                                class="slt_quantity form-control"
                                                                                min="0" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-12 col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="">Ghi chú:</label>
                                                                            <textarea name="batch[1][product][{{ $pro_odd_id }}][description]" class="slt_note form-control"
                                                                                placeholder="Ghi chú"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    class="widget-control-actions mt-3 d-flex justify-content-end">
                                                                    <x-core::button type="button" :outlined="true"
                                                                        class="widget-control-delete">
                                                                        {{ trans('packages/widget::widget.delete') }}
                                                                    </x-core::button>
                                                                </div>
                                                            </x-core::form.fieldset>
                                                            <div class="widget-description mt-1">
                                                                <x-core::form.helper-text>
                                                                    Số lượng còn lại: <strong
                                                                        class="content-current-quantity">{{ count($productGroup) }}</strong>
                                                                </x-core::form.helper-text>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <script>
                                                window.products = @json($products);
                                                window.productBatchs = @json($productBatchs);
                                            </script>
                                        </div>
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
                                    @if(empty($receipt->productBatch) || count($receipt->productBatch) === 0)
                                    <div class="group-btn">
                                        <button id="btn_cancel_receipt" type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancel-receipt"
                                        @php
                                        echo Auth::user()->hasPermission('receipt-product.cancel') ? '' : 'disabled';
                                        @endphp
                                        >
                                            <i class="fa-solid fa-reply"></i>
                                            Từ chối</button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0 w-100">
                                    <button class="btn btn-link w-100" data-bs-toggle="collapse"
                                        data-bs-target="#collapseBatch" aria-expanded="false"
                                        aria-controls="collapseBatch">
                                        Danh sách các lô đã nhập
                                    </button>
                                </h5>
                            </div>
                            <div class="collapse" id="collapseBatch">
                                <div class="card-body">
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
                            <form id="form__receipt" action="{{ route('receipt-product.approved', $receipt) }}"
                                method="post">
                                @csrf
                                <div id="qr_ids_wrapper"></div>
                                <input type="text" name="proposal_code" value="{{ $receipt->proposal_code }}" hidden>
                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="card-header flexbox-grid-default">
                                            <div class="flexbox-auto-right mr5">
                                                <label
                                                    class="h4 title-product-main text-no-bold">{{ __('Xác nhận thực nhập sản phẩm cho kho') }}
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
                                        <div class="card meta-boxes mb-3" id="gallery_wrap">
                                            <div class="card-header">
                                                <h4 class="card-title">
                                                    Hình ảnh đính kèm
                                                </h4>
                                            </div>

                                            <div class="card-body">
                                                <input id="gallery-data" class="form-control" name="gallery"
                                                    type="hidden" value="[]">
                                                <div>
                                                    <div class="list-photos-gallery">
                                                        <div class="row" id="list-photos-items">
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="#" class="btn_select_gallery">Chọn hình ảnh</a>
                                                        <a href="#"
                                                            class="text-danger reset-gallery hidden">Reset</a>
                                                    </div>
                                                </div>

                                                <div class="modal fade modal-blur" id="edit-gallery-item" tabindex="-1"
                                                    data-select2-dropdown-parent="true" style="display: none;"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable "
                                                        role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Cập nhật mô tả cho hình ảnh</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <input type="text" class="form-control"
                                                                    id="gallery-item-description" placeholder="Mô tả...">
                                                            </div>

                                                            <div class="modal-footer">
                                                                <div class="btn-list">
                                                                    <button class="btn btn-danger" type="button"
                                                                        id="delete-gallery-item">Xoá hình ảnh này</button>
                                                                    <button class="btn" type="button"
                                                                        data-bs-dismiss="modal">Thoát</button>
                                                                    <button class="btn btn-primary" type="button"
                                                                        id="update-gallery-item">Cập nhật</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- <div class="card-body option_sample">
                                        <div class="form-group">
                                            <label for="">Tuỳ chọn tạo lô hàng:</label>
                                            <div class="d-flex" style="gap: 15px;">
                                                <div class="item">
                                                    <input type="radio" name="create-batch" value="custom"
                                                        id="custom" checked>
                                                    <label for="custom">Custom</label>
                                                </div>
                                                <div class="item">
                                                    <input type="radio" name="create-batch" value="sample"
                                                        id="sample">
                                                    <label for="sample">Tạo lô mẫu</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="main__contain-batch">
                                    </div> --}}

                                    <div class="wrapper_scan_qrcode ">
                                        <div class="d-flex gap-3 p-3">
                                            <button class="btn btn-primary" id="submit_btn" data-bs-toggle="modal"
                                                data-bs-target="#receipt-actual" type="button">Xác nhận</button>

                                            {{-- @if($receipt->from_product_issue !== 1) --}}
                                            @if($checkScanQR === true)
                                                <a type="button" class="btn btn-primary btn_scan_qrcode d-inline"
                                                    data-bs-toggle="modal" href="#QrScanReceiveModal"
                                                    id="open_scan_modal_batch_warehouse" data-batch="0"
                                                    data-direction="left">
                                                    <span class="me-2">
                                                        <i class="fa-solid fa-qrcode"></i>
                                                    </span>
                                                    Quét QR
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- <div class="card-body pd-all-20 p-none-t">
                                        <div class="mt10">
                                            <button class="btn btn-primary" id="submit_btn" data-bs-toggle="modal" data-bs-target="#receipt-actual"
                                            <button class="btn btn-primary" id="submit_btn" data-bs-toggle="modal" data-bs-target="#receipt-actual"
                                                type="button">Xác nhận</button>
                                        </div>
                                    </div> --}}
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('plugins/qr-scan::scan-batch', [
        'batches' => $productBatchs,
        'wh_departure_id' => $receipt->wh_departure_id,
        'warehouse_id' => $receipt->warehouse_id,
        'warehouse' => $receipt,
        'url_receipt_detail' => '/api/v1/get-created-shipment/' . $receipt->id,
        'print_qrcode' => '/api/v1/print-qrcode-for-batch',
    ])
    @include('plugins/qr-scan::scan-create-batch')

    <!-- Modal cancel receipt -->
    <div class="modal fade" id="cancel-receipt" tabindex="-1" role="dialog" aria-labelledby="cancel-receipt"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('receipt-product.cancel', $receipt) }}" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận từ chối nhập kho</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Lý do</label>
                            <textarea name="reasoon" class="form-control" placeholder="Ghi rõ lý do" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-danger">Xác nhận</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="receipt-actual" tabindex="-1" role="dialog" aria-labelledby="receipt-actual"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận hoàn tất nhập kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <strong>Bạn có chắn chắc xác nhận hoàn thành việc nhập sản phẩm vào trong kho không?</strong>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" id="btn_actual" class="btn btn-danger">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>

@stop
@push('footer')
    <script>
        'use strict';
        var BWidget = BWidget || {};
        BWidget.routes = {
            'delete': '{{ route('widgets.destroy', ['ref_lang' => BaseHelper::stringify(request()->input('ref_lang'))]) }}',
            'save_widgets_sidebar': '{{ route('widgets.save_widgets_sidebar', ['ref_lang' => BaseHelper::stringify(request()->input('ref_lang'))]) }}'
        };
    </script>
@endpush

