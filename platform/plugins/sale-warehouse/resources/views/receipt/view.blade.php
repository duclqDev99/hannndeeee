@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\Base\Facades\BaseHelper;
        use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    @endphp
    <style>
        .flexbox-grid-default {
            position: relative;
        }

        .card-notify-year {
            position: absolute;
            right: -15px;
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

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .product {
            flex: 1 0 calc(50% - 20px);
            /* Chia layout thành 3 cột */
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .product .d-flex {
            display: flex;
        }

        .product .image img {
            max-width: 100px;
        }

        .product .details {
            flex-grow: 1;
        }

        .product .name {
            font-weight: bold;
        }

        .product .attributes,
        .product .sku,
        .product .quantity {
            font-size: 14px;
        }

        .product .attributes {
            color: #666;
        }

        .product .sku {
            color: #999;
        }

        .product .quantity {
            color: #333;
            font-weight: bold;
        }

        /* Đáp ứng: Hiển thị một cột trên màn hình nhỏ */
        @media screen and (max-width: 768px) {
            .product {
                flex: 1 0 calc(50% - 20px);
                /* Chia layout thành 2 cột */
            }
        }


        .status-container {
            position: relative;
            margin-top: 20px;
        }

        .status-tag {
            position: absolute;
            font-size: 200%;
            left: 50%;
            transform: translateX(-50%) rotate(-45deg);
            text-align: right;
            color: #fff;
            padding: 5px 20px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
            user-select: none;
            opacity: 0.2;
        }
    </style>
    {{-- justify-content-center --}}
    <div class="widget__view row justify-content-center ">
        <div class="card col-10">
            <div class="card-header">
                <div>
                    <h2 class="title">Thông tin phiếu nhập kho
                        {{ BaseHelper::clean(get_proposal_receipt_product_code($productIssue->receipt_code)) }}
                        <span class="status-container status-tag status-{{ $productIssue->status }}">
                            @php
                                echo $productIssue->status->toHtml();
                            @endphp

                        </span>
                    </h2>
                    <h3> Mục đích nhập kho:
                        {{ $productIssue->title }}
                    </h3>

                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho xuất:</label>
                            <strong> {{ $productIssue->warehouse->name }} -
                                {{ $productIssue->warehouse?->hub?->name }}
                            </strong>
                        </div>
                        <div>
                            Người đề xuất:
                            <strong>{{ $productIssue->invoice_issuer_name }}</strong>
                        </div>
                        <div>
                            Ngày tạo:
                            {{ date('d-m-Y', strtotime($productIssue->created_at)) }}
                        </div>
                        <div class="info-group">
                            <label>Mã đơn hàng:</label>
                            <strong>{{ $productIssue->general_order_code ?: '—' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Kho nhận:</label>
                            <strong> {{ $productIssue->warehouse_name }}
                                - {{ $productIssue->warehouseReceipt->saleWarehouse?->name }}

                            </strong>
                        </div>
                        <div>
                            Người duyệt:
                            <strong>{{ $productIssue->invoice_confirm_name ?: '—' }}</strong>
                        </div>
                        <div>
                            Ngày duyệt:
                            {{ $productIssue->date_confirm ? date('d-m-Y', strtotime($productIssue->date_confirm)) : '—' }}
                        </div>

                        @if ($productIssue->reason_cancel)
                            <div> Lý do từ chối: <strong style="color: red">
                                    {{ $productIssue->reason_cancel }}</strong></div>
                        @endif
                    </div>

                </div>
            </div>
            @php
                $a = 12;
                if (isset($actualIssue)) {
                    $a = 6;
                }
            @endphp
            <div class="card-body row">
                @isset($actualIssueHub)
                    <div class="col-lg-{{ $a }} col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">
                                            <span class="card-notify-year red">Phiếu nhập kho</span>
                                            <div class="card-body pd-all-20 p-none-t m-3" id="accordion ">
                                                @php
                                                    $totalIssue = 0;
                                                    $groupedProducts = $batchs
                                                        ->filter(function ($detail) {
                                                            return $detail->batch_id > 0;
                                                        })
                                                        ->groupBy('batch_id');
                                                @endphp
                                                <h3>Danh sách lô</h3>
                                                @if (count($batchs) > 0)
                                                    <div id="batchTable" class="accordion">
                                                        @foreach ($groupedProducts as $batch_id => $products)
                                                            @php
                                                                $isBatch = $products
                                                                    ->where('is_batch', 1)
                                                                    ->isNotEmpty();
                                                            @endphp
                                                            <div class="accordion-item">
                                                                <div class="accordion-header d-flex align-items-center justify-content-between"
                                                                    id="heading{{ $batch_id }}">
                                                                    <button class="accordion-button collapsed" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#collapse{{ $batch_id }}"
                                                                        aria-expanded="true"
                                                                        aria-controls="collapse{{ $batch_id }}">
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-center w-100">
                                                                            <span>
                                                                                {{ $products?->first()?->batch?->batch_code }}
                                                                                -
                                                                                {{ $products?->first()?->batch?->quantity }}
                                                                                sản
                                                                                phẩm
                                                                            </span>
                                                                            <span>
                                                                                Ngày tạo:
                                                                                {{ date('d-m-Y', strtotime($products->first()->batch->created_at)) }}
                                                                            </span>
                                                                        </div>
                                                                    </button>
                                                                </div>

                                                                <div id="collapse{{ $batch_id }}"
                                                                    class="accordion-collapse collapse"
                                                                    aria-labelledby="heading{{ $batch_id }}"
                                                                    data-bs-parent="#batchTable">
                                                                    <div class="accordion-body">
                                                                        <div class="product-list">
                                                                            @php
                                                                                $displayedProducts = [];
                                                                            @endphp
                                                                            @foreach ($products as $product)
                                                                                @if ($product->is_batch == 0 && !in_array($product->product_id, $displayedProducts))
                                                                                    @php
                                                                                        $displayedProducts[] =
                                                                                            $product->product_id;
                                                                                        $productCount = 0;
                                                                                    @endphp
                                                                                    @foreach ($products as $innerProduct)
                                                                                        @if ($innerProduct->product_id == $product->product_id)
                                                                                            @php
                                                                                                $totalIssue++;
                                                                                                $productCount++;
                                                                                            @endphp
                                                                                        @endif
                                                                                    @endforeach
                                                                                    <div class="product">
                                                                                        <div class="d-flex">
                                                                                            <div
                                                                                                class="image align-items-center mb-3">
                                                                                                <img class="thumb-image thumb-image-cartorderlist"
                                                                                                    src="{{ RvMedia::getImageUrl($product?->product?->parentProduct?->first()->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                                    width="60px"
                                                                                                    alt="{{ $product->product->name }}">
                                                                                            </div>
                                                                                            <div class="details">
                                                                                                <span
                                                                                                    class="name">{{ $product->product->name }}</span>
                                                                                                <span class="attributes">
                                                                                                    @foreach ($product->product->variationProductAttributes as $attribute)
                                                                                                        @if ($attribute->color)
                                                                                                            {{ 'Màu: ' . $attribute->title }}
                                                                                                        @endif
                                                                                                    @endforeach

                                                                                                    @foreach ($product->product->variationProductAttributes as $attribute)
                                                                                                        @if (!$attribute->color)
                                                                                                            {{ 'Size: ' . $attribute->title }}
                                                                                                        @endif
                                                                                                    @endforeach
                                                                                                </span>
                                                                                                <span class="sku">SKU:
                                                                                                    {{ $product->product->sku }}</span>
                                                                                                <span
                                                                                                    class="quantity">{{ $productCount }}
                                                                                                    sản phẩm</span>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div id="batchTable" class="accordion">
                                                        @foreach ($actualIssueHub->autualDetail as $batch)
                                                            @php
                                                                $totalIssue = $batch?->quantity;

                                                            @endphp
                                                            <div class="accordion-item">
                                                                <div class="accordion-header d-flex align-items-center justify-content-between"
                                                                    id="heading{{ $batch?->batch?->id }}">
                                                                    <button class="accordion-button collapsed" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#collapse{{ $batch?->batch?->id }}"
                                                                        aria-expanded="true"
                                                                        aria-controls="collapse{{ $batch?->batch?->id }}">
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-center w-100">
                                                                            <span>
                                                                                {{ $batch?->batch?->batch_code }}
                                                                                -
                                                                                {{ $batch?->quantity }}
                                                                                sản
                                                                                phẩm
                                                                            </span>
                                                                            <span>
                                                                                Ngày tạo:
                                                                                {{ date('d-m-Y', strtotime($batch?->batch?->created_at)) }}
                                                                            </span>
                                                                        </div>
                                                                    </button>
                                                                </div>

                                                                <div id="collapse{{ $batch?->batch?->id }}"
                                                                    class="accordion-collapse collapse"
                                                                    aria-labelledby="heading{{ $batch?->batch?->id }}"
                                                                    data-bs-parent="#batchTable">
                                                                    <div class="accordion-body">
                                                                        <div class="product-list">
                                                                            @php
                                                                                $displayedProducts = [];
                                                                            @endphp
                                                                            <div class="product">
                                                                                <div class="d-flex">
                                                                                    <div class="image align-items-center mb-3">
                                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                                            src="{{ RvMedia::getImageUrl($batch?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                            width="60px"
                                                                                            alt="{{ $batch->product->name }}">
                                                                                    </div>
                                                                                    <div class="details">
                                                                                        <span
                                                                                            class="name">{{ $batch->product->name }}</span>
                                                                                        <span class="attributes">
                                                                                            @foreach ($batch?->product?->variationProductAttributes as $attribute)
                                                                                                @if ($attribute?->color)
                                                                                                    {{ 'Màu: ' . $attribute->title }}
                                                                                                @endif
                                                                                            @endforeach

                                                                                            @foreach ($batch?->product?->variationProductAttributes as $attribute)
                                                                                                @if (!$attribute->color)
                                                                                                    {{ 'Size: ' . $attribute->title }}
                                                                                                @endif
                                                                                            @endforeach
                                                                                        </span>
                                                                                        <span class="sku">SKU:
                                                                                            {{ $batch?->product?->sku }}</span>
                                                                                        <span
                                                                                            class="quantity">{{ $batch?->quantity }}
                                                                                            sản phẩm</span>

                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                            @php
                                                $productDetails = $batchs->filter(function ($detail) {
                                                    return $detail->batch_id == 0 && $detail->is_batch == 0;
                                                });

                                            @endphp
                                            <div class="card-body pd-all-20 p-none-t m-3">
                                                <h3 class="">Danh sách lẻ</h3>
                                                <div class="product-list">
                                                    @php
                                                        $existingProducts = [];
                                                    @endphp
                                                    @foreach ($productDetails as $detail)
                                                        @if (!in_array($detail->product_id, $existingProducts))
                                                            @php
                                                                $existingProducts[] = $detail->product_id;
                                                                $productCount = 0;
                                                            @endphp
                                                            @foreach ($productDetails as $innerProduct)
                                                                @if ($innerProduct->product->id == $detail->product_id)
                                                                    @php
                                                                        $totalIssue++;
                                                                        $productCount++;
                                                                    @endphp
                                                                @endif
                                                            @endforeach

                                                            <div class="product">
                                                                <div class="d-flex">
                                                                    <div class="image align-items-center mb-3">
                                                                        <img class="thumb-image thumb-image-cartorderlist"
                                                                            src="{{ RvMedia::getImageUrl($detail->product->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                            width="60px" alt="{{ $detail->product->name }}">
                                                                    </div>
                                                                    <div class="details">
                                                                        <span
                                                                            class="name">{{ $detail->product->name }}</span>
                                                                        <span class="attributes">
                                                                            @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                @if ($attribute->color)
                                                                                    {{ 'Màu: ' . $attribute->title }}
                                                                                @endif
                                                                            @endforeach

                                                                            @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                @if (!$attribute->color)
                                                                                    {{ 'Size: ' . $attribute->title }}
                                                                                @endif
                                                                            @endforeach
                                                                        </span>
                                                                        <span class="sku">SKU:
                                                                            {{ $detail?->product?->sku }}</span>
                                                                        <span class="quantity">{{ $productCount }}
                                                                            sản phẩm</span>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col text-end">
                                                    <strong class="float-end">Tổng số lượng: {{ $totalIssue }} sản
                                                        phẩm </strong>
                                                </div>
                                            </div>
                                            <div class="card-body pd-all-20 p-none-t">
                                                <div class="flexbox-grid-default">
                                                    <div class="flexbox-auto-right pl5">
                                                        <div class="row">

                                                            <div class="col-lg-6 col-md-12">
                                                                <div class="py-3 w-100">
                                                                    <label class="text-title-field">Ghi chú</label>
                                                                    <textarea class="form-control textarea-auto-height" name="description" rows="4" placeholder="Ghi chú"
                                                                        disabled="">{{ $productIssue->description }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 col-md-12">
                                                                <div class="py-3">
                                                                    <label class="text-title-field">Ngày dự kiến</label>
                                                                    @php
                                                                        $inputDate = $productIssue->expected_date;
                                                                        $formattedDate = date(
                                                                            'd-m-Y',
                                                                            strtotime($inputDate),
                                                                        );
                                                                    @endphp
                                                                    <input class="form-control flatpickr-input"
                                                                        data-date-format="d-m-Y" v-pre="1"
                                                                        placeholder="d-m-Y" data-input=""
                                                                        readonly="readonly" name="expected_date"
                                                                        type="text" value="{{ $formattedDate }}"
                                                                        id="expected_date" aria-invalid="false"
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
                        </div>
                    </div>

                @endisset
                @isset($actualIssue)
                    <div class="col-lg-6 col-md-12">
                        <div class="ui-layout">
                            <div class="flexbox-layout-sections">
                                <div class="flexbox-layout-section-primary mt20">
                                    <div class="card ui-layout__item">
                                        <div class="wrapper-content">
                                            <span class="card-notify-year blue">Phiếu thực nhập</span>
                                            <div class="card-body pd-all-20 p-none-t m-3" id="accordion ">
                                                @php
                                                    $totalActual = 0;
                                                    $groupedByBatch = $actualIssue->actualDetail
                                                        ->whereNotNull('batch_id')
                                                        ->groupBy('batch_id');
                                                @endphp
                                                <h3>Danh sách lô</h3>
                                                <div id="batchTable" class="accordion">
                                                    @foreach ($groupedByBatch as $batchId => $details)
                                                        @php
                                                            $totalActual++;
                                                            $batch = ProductBatch::find($batchId);
                                                            $batchQuantity = $details->sum('quantity');

                                                        @endphp
                                                        <div class="accordion-item">
                                                            <div class="accordion-header d-flex align-items-center justify-content-between"
                                                                id="heading{{ $batchId }}">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapse{{ $batchId }}"
                                                                    aria-expanded="true"
                                                                    aria-controls="collapse{{ $batchId }}">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center w-100">
                                                                        <span>
                                                                            {{ $batch?->batch_code }} -
                                                                            {{ $batchQuantity }} sản phẩm
                                                                        </span>

                                                                    </div>
                                                                </button>
                                                            </div>
                                                            <div id="collapse{{ $batchId }}"
                                                                class="accordion-collapse collapse"
                                                                aria-labelledby="heading{{ $batchId }}"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="product-list">
                                                                        @php
                                                                            $displayedProducts = [];
                                                                        @endphp
                                                                        @foreach ($details as $detail)
                                                                            @if (!in_array($detail->product_id, $displayedProducts))
                                                                                @php
                                                                                    $displayedProducts[] =
                                                                                    $detail->product_id;
                                                                                    $countProduct = $details
                                                                                    ->where(
                                                                                        'product_id',
                                                                                        $detail->product_id,
                                                                                    )
                                                                                    ->sum('quantity');
                                                                                    $productCount = 0;
                                                                                @endphp
                                                                                <div class="product">
                                                                                    <div class="d-flex">
                                                                                        <div
                                                                                            class="image align-items-center mb-3">
                                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                                src="{{ RvMedia::getImageUrl($detail->product?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                                width="60px"
                                                                                                alt="{{ $detail->product->name }}">
                                                                                        </div>
                                                                                        <div class="details">
                                                                                            <span
                                                                                                class="name">{{ $detail->product->name }}</span>
                                                                                            <span class="attributes">
                                                                                                @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                                    @if ($attribute->color)
                                                                                                        {{ 'Màu: ' . $attribute->title }}
                                                                                                    @endif
                                                                                                @endforeach

                                                                                                @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                                    @if (!$attribute->color)
                                                                                                        {{ 'Size: ' . $attribute->title }}
                                                                                                    @endif
                                                                                                @endforeach
                                                                                            </span>
                                                                                            <span class="sku">SKU:
                                                                                                {{ $detail->product->sku }}</span>
                                                                                            <span
                                                                                                class="quantity">{{ $countProduct }}
                                                                                                sản phẩm</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="card-body pd-all-20 p-none-t m-3" id="accordion ">
                                                @foreach ($productIssue->receiptDetail as $orderProduct)
                                                    @php
                                                        $groupedByOddActual = $actualIssue->actualDetail->filter(
                                                            function ($detail) {
                                                                return $detail->batch_id == null;
                                                            },
                                                        );
                                                    @endphp
                                                @endforeach
                                                <div id="table-wrapper" class="table-wrap">
                                                    <h3 class="">Danh sách lẻ</h3>
                                                    <div class="product-list">
                                                        @php
                                                            $existingProducts = [];
                                                        @endphp
                                                        @foreach ($groupedByOddActual as $detail)
                                                            @if (!in_array($detail->product_id, $existingProducts))
                                                                @php
                                                                    $existingProducts[] = $detail->product_id;
                                                                    $productCount = 0;
                                                                @endphp
                                                                @foreach ($groupedByOddActual as $innerProduct)
                                                                    @if ($innerProduct->product->id == $detail->product_id)
                                                                        @php
                                                                            $productCount++;
                                                                        @endphp
                                                                    @endif
                                                                @endforeach
                                                                <div class="product">
                                                                    <div class="d-flex">
                                                                        <div class="image align-items-center mb-3">
                                                                            <img class="thumb-image thumb-image-cartorderlist"
                                                                                src="{{ RvMedia::getImageUrl($detail->product?->parentProduct?->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                                width="60px"
                                                                                alt="{{ $detail->product->name }}">
                                                                        </div>
                                                                        <div class="details">
                                                                            <span
                                                                                class="name">{{ $detail->product->name }}</span>
                                                                            <span class="attributes">
                                                                                @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                    @if ($attribute->color)
                                                                                        {{ 'Màu: ' . $attribute->title }}
                                                                                    @endif
                                                                                @endforeach

                                                                                @foreach ($detail->product->variationProductAttributes as $attribute)
                                                                                    @if (!$attribute->color)
                                                                                        {{ 'Size: ' . $attribute->title }}
                                                                                    @endif
                                                                                @endforeach
                                                                            </span>
                                                                            <span class="sku">SKU:
                                                                                {{ $detail->product->sku }}</span>
                                                                            <span class="quantity">{{ $productCount }}
                                                                                sản phẩm</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body pd-all-20 p-none-t border-top-title-main">
                                                <div class="card meta-boxes mb-3">
                                                    <div class="card-header">
                                                        <h4 class="card-title">
                                                            Hình ảnh đính kèm
                                                        </h4>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="list-photos-gallery">
                                                            <div class="row g-2">

                                                                @if ($actualIssue->image)
                                                                    @php
                                                                        $images = json_decode($actualIssue->image);
                                                                    @endphp
                                                                    @if ($images)
                                                                        @foreach ($images as $image)
                                                                            <div
                                                                                class="col-lg-2 col-md-3 col-4 gallery-image-item-handler mb-2">
                                                                                <div class="custom-image-box mage-box">
                                                                                    <img class="preview-image default-image"
                                                                                        src="{{ RvMedia::getImageUrl($image, 'thumb') }}"
                                                                                        alt="Preview image">
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @else
                                                                    <div
                                                                        class="col-lg-2 col-md-3 col-4 gallery-image-item-handler mb-2">
                                                                        <div class="custom-image-box mage-box">
                                                                            <img class="preview-image default-image"
                                                                                src="{{ RvMedia::getDefaultImage() }}"
                                                                                alt="Preview image">
                                                                        </div>
                                                                    </div>
                                                                @endif
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
                    </div>
                </div>
            </div>
        @endisset
    </div>
    </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var collapseButtons = document.querySelectorAll('[data-toggle="collapse"]');
            collapseButtons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    var target = button.getAttribute('data-target');
                    var collapseElement = document.querySelector(target);
                    if (collapseElement) {
                        collapseElement.classList.toggle('show');
                    }
                });
            });
        });
        $(document).ready(function() {
            $(".batch-header").click(function() {
                var batchId = $(this).data("batch-id");
                var detailsRow = $("#batch-details-" + batchId);
                detailsRow.toggle();
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const widget = document.querySelectorAll('.widget__view .ui-layout__item');



            if (widget && widget.length === 2) {
                const wp_porposal = widget[0];
                const wp_receipt = widget[1];

                const itemHighlightPorposal = wp_porposal.querySelectorAll('.check__highlight');
                const itemHighlightReceipt = wp_receipt.querySelectorAll('.check__highlight');

                for (let index = 0; index < itemHighlightPorposal.length; index++) {
                    if (itemHighlightPorposal[index].textContent !== itemHighlightReceipt[index].textContent) {
                        itemHighlightPorposal[index].style.color = 'red';
                        itemHighlightPorposal[index].style.fontSize = '1.2em';
                        itemHighlightPorposal[index].style.fontWeight = 'bold';
                        itemHighlightPorposal[index].style.textDecoration = 'underline';

                        itemHighlightReceipt[index].style.color = 'red';
                        itemHighlightReceipt[index].style.fontSize = '1.2em';
                        itemHighlightReceipt[index].style.fontWeight = 'bold';
                        itemHighlightReceipt[index].style.textDecoration = 'underline';
                    }
                }
            }

        })
    </script>
@stop
