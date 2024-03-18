<div class="modal fade modal-blur" id="warehouseIssueModal" tabindex="-1" role="dialog"
    data-select2-dropdown-parent="true" aria-modal="true">
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
                                                    <th scope="col">Xuất theo lô</th>
                                                    <th scope="col">Số lượng sản phẩm</th>
                                                    <th scope="col">Đã quét</th>
                                                    <th scope="col">Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody id="product_batch_table_list"> </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card  border-0 position-relative">
                                    <div class="card-header border border-bottom-0">
                                        <h4 class="card-title fw-bolder">Lô xuất đi</h4>
                                    </div>
                                    <div class="card-body p-0 overflow-y-auto">
                                        {{-- <button id="drag_batch_info" type="button" class="btn btn-success">
                                                  BATCH0001 <span class="badge bg-light mx-2">43 SP</span>
                                              </button> --}}
                                        <table class="mb-0 table table-bordered ">
                                            <thead>
                                                <tr>
                                                    <th scope="col">STT</th>
                                                    <th scope="col">Thông tin lô</th>
                                                    <th scope="col">Kho hiện tại</th>
                                                    <th scope="col">Số lượng SP</th>
                                                    <th scope="col">Tùy chọn</th>
                                                </tr>
                                            </thead>
                                            <tbody id="batch_table_list"> </tbody>
                                        </table>
                                        <div id="empty_scan_batch_message"
                                            class="flex-grow-1 border border-top-0 d-flex align-items-center justify-content-center">
                                            <p class="my-3">Chưa có thông tin QR được quét!</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Tab sp xuất lẻ --}}
                            <div class="tab-pane fade" id="product-tab" role="tabpanel"
                                aria-labelledby="pills-profile-tab">
                                <div class="overflow-auto" id="table_wrapper" >
                                    <div class="card border-0 position-relative mb-3">
                                        <div class="card-header border border-bottom-0">
                                            <h4 class="card-title fw-bolder">Thông tin đề xuất</h4>
                                        </div>
                                        <div class="card-body p-0 ">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">STT</th>
                                                        <th scope="col">Thông tin sản phẩm</th>
                                                        <th scope="col">Số lượng xuất dự kiến</th>
                                                        <th scope="col">Đã quét</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="product_table_list"> </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card border-0 position-relative">
                                        <div class="card-header border border-bottom-0">
                                            <h4 class="card-title fw-bolder">Sản phẩm xuất đi</h4>
                                        </div>
                                        <div class="card-body p-0 ">
                                            <table class="mb-0 table table-bordered ">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">STT</th>
                                                        <th scope="col">Tên sản phẩm</th>
                                                        <th scope="col">trạng thái</th>
                                                        <th scope="col">Kho hiện tại</th>
                                                        <th scope="col">Tùy chọn</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="table_list"> </tbody>
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

                <button id="save_qr_scan_btn" class="btn btn-primary" type="button" name="save"
                    id="store-related-attributes-button" disabled>
                    Lưu
                </button>
                <button class="btn" type="button" id="reset_btn">
                    Reset
                </button>
            </div>
        </div>
    </div>
</div>

@push('header')
    @php
        Assets::addStylesDirectly(['vendor/core/plugins/qr-scan/css/scan-loading.css'])
        ->addScriptsDirectly(['vendor/core/plugins/qr-scan/js/scan-warehouse-issue-start.js'])
    @endphp
@endpush

@push('footer')
    <script>
        window.warehouse_id = @json($warehouse_id);
        window.warehouse_type = @json($warehouse_type);
        window.products = @json($data);
    </script>
@endpush
