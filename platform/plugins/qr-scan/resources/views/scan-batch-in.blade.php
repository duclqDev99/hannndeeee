@push('header')
    @php
        Assets::addStylesDirectly(['vendor/core/plugins/qr-scan/css/scan-loading.css']);
    @endphp
@endpush

@push('footer')
    <script>
        window.batches = @json($batches);
        window.wh_departure_id = @json($wh_departure_id);
        window.wh_warehouse_id = @json($warehouse_id);
        window.warehouse = @json($warehouse);
    </script>
    @php
        Assets::addScriptsDirectly([
            'vendor/core/plugins/qr-scan/js/scan-batch-in.js'
        ]);
    @endphp
@endpush

<div class="modal fade modal-blur" id="scanBatchInModal" tabindex="-1" data-select2-dropdown-parent="true" aria-modal="true"
    role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quét QR nhập lô</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="number" id="modal_batch_id" hidden>
                <div>
                    <div class="row" id="box_scan">
                        <div class="col overflow-auto" id="table_wrapper" style="max-height: 400px">
                            <div class="card border-0 position-relative mb-3">
                                <div class="card-header border border-bottom-0">
                                    <h4 class="card-title fw-bolder">Lô sản phẩm <span id="batch_name"></span></h4>
                                </div>
                                <div class="card-body p-0 ">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">STT</th>
                                                <th scope="col">Mã lô</th>
                                                <th scope="col">Nhập từ kho</th>
                                                <th scope="col">Trạng thái</th>
                                                <th scope="col">Tùy chọn</th>
                                            </tr>
                                        </thead>
                                        <tbody id="batch_table_list"> </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                        {{-- Qr Camera --}}
                        <div class="col-3 d-flex flex-column">
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
                    <div id="box_batch_info" class="row " style="display: none">
                        <div>
                            <button id="back_box_btn" class="btn btn-primary" data-bs-toggle="tooltip"
                                title="Trang trước">
                                <i class="fa-solid fa-backward"></i>
                            </button>
                        </div>
                        <div id="content" style="min-height: 50vh"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="save_qr_scan_btn" name="save" class="btn btn-primary" type="button" disabled>
                    Lưu
                </button>
                <button id="reset_batch_scan_btn" class="btn" type="button">
                    Reset
                </button>
            </div>
        </div>
    </div>
</div>


