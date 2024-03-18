<div class="modal fade modal-blur" id="warehouseIssueModal" tabindex="-1" role="dialog" data-select2-dropdown-parent="true"
    aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quét QR xuất kho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="box_scan">
                    <div class="row  position-relative">
                        <div class="col tab-content overflow-y-auto" style="max-height: 400px;" id="pills-tabContent">
                            {{-- Tab lô xuất --}}
                            <div class="tab-pane fade active" id="batch-tab" role="tabpanel"
                                aria-labelledby="pills-home-tab">
                                <div class="card border-0 position-relative mb-3">
                                    <div class="card-header border border-bottom-0">
                                        <h4 class="card-title fw-bolder">Thông tin đề xuất</h4>
                                    </div>
                                    <div class="card-body p-0 overflow-y-auto">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th scope="col">STT</th>
                                                    <th scope="col">Thông tin sản phẩm</th>
                                                    <th scope="col">Số lượng sản phẩm</th>
                                                    <th scope="col">Đã quét</th>
                                                    <th scope="col">Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody id="product_batch_table_list"> </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="container mt-5">
                                    <div class="card border-0 position-relative">
                                        <div class="card-header border border-bottom-0">
                                            <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link active" id="batch-tb" data-toggle="tab"
                                                        href="#batch" role="tab" aria-controls="batch"
                                                        aria-selected="true">Lô xuất đi</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="product-tab" data-toggle="tab"
                                                        href="#product" role="tab" aria-controls="product"
                                                        aria-selected="false">Sản phẩm xuất đi</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-body p-0 overflow-y-auto">
                                            <div class="tab-content" id="myTabContent">
                                                <!-- Batch Tab Pane -->
                                                <div class="tab-pane fade show active" id="batch" role="tabpanel"
                                                    aria-labelledby="batch-tb">
                                                    <table class="mb-0 table table-bordered">
                                                        <!-- Your existing table content for batch -->
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">STT</th>
                                                                <th scope="col">Thông tin sản phẩm</th>
                                                                <th scope="col">Lô được quét</th>
                                                                <th scope="col">Số lượng SP</th>
                                                                <th scope="col">Tùy chọn</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="batch_table_list"></tbody>
                                                    </table>
                                                    <div id="empty_scan_batch_message"
                                                        class="flex-grow-1 border border-top-0 d-flex align-items-center justify-content-center">
                                                        <p class="my-3">Chưa có thông tin QR được quét!</p>
                                                    </div>
                                                </div>
                                                <!-- Product Tab Pane -->
                                                <div class="tab-pane fade" id="product" role="tabpanel"
                                                    aria-labelledby="product-tab">
                                                    <table class="mb-0 table table-bordered">
                                                        <!-- Your existing table content for product -->
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">STT</th>
                                                                <th scope="col">Tên sản phẩm</th>
                                                                <th scope="col">Trạng thái</th>
                                                                <th scope="col">Kho hiện tại</th>
                                                                <th scope="col">Tùy chọn</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table_list"></tbody>
                                                    </table>
                                                    <div id="empty_scan_product_mess"
                                                        class="flex-grow-1 border border-top-0 d-flex align-items-center justify-content-center">
                                                        <p class="my-3">Chưa có thông tin QR được quét!</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        {{-- Qr Camera --}}
                        <div class="col-3 d-none d-md-flex flex-column position-sticky top-0">
                            <div class="card">
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
                <div id="box_batch_info" class="row " style="display: none">
                    <div>
                        <button id="back_box_btn" class="btn btn-primary" data-bs-toggle="tooltip"
                            title="Trang trước">
                            <i class="fa-solid fa-backward"></i>
                        </button>
                    </div>
                    <div id="content" style="min-height: 85vh"></div>
                </div>
            </div>
            <div class="modal-footer">

                <button id="save_qr_batch" class="btn btn-primary save_qr_batch" type="button" name="save_batch"
                    id="store-related-attributes-button" disabled>
                    Tạo lô
                </button>
                <button id="save_qr_scan_btn" class="btn btn-primary save_qr_scan_btn" type="button" name="save"
                    id="store-related-attributes-button" disabled>
                    Lưu
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade modal-blur" id="qrList" tabindex="-1" role="dialog" data-select2-dropdown-parent="true"
    aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Danh sách đã quét</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="batchModalBody">
                @php
                    $groupedProducts = $qrcode
                        ->filter(function ($detail) {
                            return $detail->batch_id > 0;
                        })
                        ->groupBy('batch_id');
                @endphp
                <h4>Danh sách lô</h4>
                <div id="batchTable" class="accordion">
                    @foreach ($groupedProducts as $batch_id => $products)
                        @php
                            $isBatch = $products->where('is_batch', 1)->isNotEmpty();
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $batch_id }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse{{ $batch_id }}" aria-expanded="true"
                                    aria-controls="collapse{{ $batch_id }}">
                                    {{ $products?->first()?->batch?->batch_code }} -
                                    {{ $products?->first()?->batch?->quantity }} sản phẩm
                                </button>
                            </h2>
                            <div id="collapse{{ $batch_id }}" class="accordion-collapse collapse"
                                aria-labelledby="heading{{ $batch_id }}" data-bs-parent="#batchTable">
                                <div class="accordion-body">
                                    <div class="product-list">
                                        @php
                                            $displayedProducts = [];
                                        @endphp
                                        @foreach ($products as $product)
                                            @if ($product->is_batch == 0 && !in_array($product->product_id, $displayedProducts))
                                                @php
                                                    $displayedProducts[] = $product->product_id;
                                                    $productCount = 0;
                                                @endphp
                                                @foreach ($products as $innerProduct)
                                                    @if ($innerProduct->product_id == $product->product_id)
                                                        @php
                                                            $productCount++;
                                                        @endphp
                                                    @endif
                                                @endforeach
                                                <div class="product">
                                                    <span class="name">{{ $product->product->name }}</span>
                                                    <span class="attributes">
                                                        @foreach ($product->product->variationProductAttributes as $attribute)
                                                            @if ($attribute->color)
                                                                {{ 'Màu: ' . $attribute->title }}
                                                            @else
                                                                {{ 'Size: ' . $attribute->title }}
                                                            @endif
                                                        @endforeach
                                                    </span>
                                                    <span class="sku">SKU: {{ $product->product->sku }}</span>
                                                    <span class="quantity">{{ $productCount }} sản phẩm</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

                <h4>Danh sách lẻ</h4>
                <div id="productTable">
                    <div class="product-list">
                        @php
                            $existingProducts = [];
                        @endphp

                        @foreach ($groupedOdd as $odd)
                            @if (!in_array($odd->product->id, $existingProducts))
                                @php
                                    $existingProducts[] = $odd->product->id;
                                    $productCount = 0;
                                @endphp
                                @foreach ($groupedOdd as $innerProduct)
                                    @if ($innerProduct->product->id == $odd->product_id)
                                        @php
                                            $productCount++;
                                        @endphp
                                    @endif
                                @endforeach
                                <div class="product">
                                    <span class="name">{{ $odd->product->name }}</span>
                                    <span class="attributes">
                                        Màu:
                                        {{ $odd->product->variationProductAttributes[0]->title ?? '---' }}
                                        -
                                        Size:
                                        {{ $odd->product->variationProductAttributes[1]->title ?? '---' }}
                                    </span>
                                    <span class="sku">SKU: {{ $odd->product->sku }}</span>
                                    <span class="quantity">{{ $productCount }} sản phẩm</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<style>
    .accordion-toggle {
        cursor: pointer;
    }

    .accordion-toggle:hover {
        background-color: #f5f5f5;
    }

    .collapse {
        margin: 10px;
    }

    /* Thiết lập các thuộc tính mặc định cho các phần tử */
    .product-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .product {
        width: calc(33.33% - 20px);
        /* Chia layout thành 3 cột */
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .product .name {
        font-weight: bold;
    }

    .product .attributes {
        font-size: 14px;
        color: #666;
    }

    .product .sku {
        font-size: 14px;
        color: #999;
    }

    .product .quantity {
        font-size: 14px;
        color: #333;
        font-weight: bold;
    }

    /* Đáp ứng: Hiển thị một cột trên màn hình nhỏ */
    @media screen and (max-width: 768px) {
        .product {
            width: calc(50% - 20px);
            /* Chia layout thành 2 cột */
        }
    }

    /* Đáp ứng: Hiển thị một cột trên màn hình rất nhỏ */
    @media screen and (max-width: 480px) {
        .product {
            width: 100%;
            /* Hiển thị 1 cột */
        }
    }
</style>
@push('header')
    @php
        Assets::addStylesDirectly(['vendor/core/plugins/qr-scan/css/scan-loading.css'])->addScriptsDirectly([
            'vendor/core/plugins/qr-scan/js/scan-warehouse-issue.js',
        ]);
    @endphp
@endpush

@push('footer')
    <script>
        window.warehouse_id = @json($warehouse_id);
        window.warehouse_type = @json($warehouse_type);
        window.warehouse_receipt_type = @json($warehouse_receipt_type);
        window.url = @json($url_confirm);
        window.products = @json($data);
        window.date_type  = @json($date_type);
        window.dateAcceipt  = @json($dateAcceipt);
    </script>
@endpush
