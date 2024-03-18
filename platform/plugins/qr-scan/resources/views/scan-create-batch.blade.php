<div class="modal fade modal-blur" id="QrScanReceiveModal" tabindex="-1" data-select2-dropdown-parent="true"
    aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content" style="min-height: 95vh">
            <div class="modal-header">
                <h5 class="modal-title">Quét QR cho sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column">
                <div class="row flex-grow-1">
                    <div class="col d-flex flex-column position-relative" id="table_wrapper">

                        <div id="product_by_batch_box" class=" card border-0 position-relative mb-3"
                            >
                            <div class="card-header border border-bottom-0">
                                <h4 id="scan-info-title" class="card-title fw-bolder">Sản phẩm đề xuất</h4>
                            </div>
                            <div class="card-body p-0 ">
                                <table id="table-info" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col">STT</th>
                                            <th scope="col">Thông tin</th>
                                            <th scope="col">Số lượng còn lại</th>
                                            <th scope="col">QR đã quét</th>
                                        </tr>
                                    </thead>
                                    <tbody class="body-info"> </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex-grow-1 d-flex flex-column card shadow-sm border-0 position-relative">
                            <div class="card-header">
                                <h4 class="card-title fw-bolder">Sản phẩm đã quét</h4>
                            </div>
                            <div class="card-body d-flex flex-column p-0 border-0">
                                <table id="table-scanned" class="mb-0 table">
                                    <thead>
                                        <tr>
                                            <th scope="col">STT</th>
                                            <th scope="col">Tên sản phẩm</th>
                                            <th scope="col">trạng thái</th>
                                            <th scope="col">Thông tin kho</th>
                                            <th scope="col">Tùy chọn</th>
                                        </tr>
                                    </thead>
                                    <tbody class="body-scanned"> </tbody>
                                </table>
                                <div
                                    class="empty_scanned_message flex-grow-1 border-0 d-flex align-items-center justify-content-center">
                                    <p class="my-3">Trống</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Qr Camera --}}
                    <div class="col-3 position-relative">
                        <div class="position-sticky top-0 d-flex flex-column gap-3">
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
            </div>
            <div class="modal-footer">
                <button id="btn_create_batch_and_receipt" name="create-batch" class="btn btn-success" type="button" disabled>
                    Tạo lô & nhập
                </button>
                <button id="save_qr_scan_btn" name="save" class="btn btn-primary" type="button" disabled>
                    Xác nhận nhập
                </button>

            </div>
        </div>
    </div>
</div>

@push('header')
    @php
        Assets::addStylesDirectly(['vendor/core/plugins/qr-scan/css/scan-loading.css'])->addScriptsDirectly(['vendor/core/plugins/qr-scan/js/scan-create-batch.js']);
    @endphp
@endpush
