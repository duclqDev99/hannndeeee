<div class="modal fade p-0 " id="receiptScanModal" tabindex="-1" role="dialog">
    {{-- <audio id="mySound" src="{{ asset('storage/audio/scanner.mp3') }}"></audio> --}}

    <div class="modal-dialog modal-lg modal-fullscreen p-0" role="document">
        <div class="modal-content">
            <div class="modal-body bg-white p-0 ">
                <div class="position-relative w-100 bg-light d-flex align-items-center justify-content-center overflow-hidden"
                    style="height: 350px">
                    <canvas id="canvas" class="w-100 h-100"></canvas>
                    <div id="camera-loading" class="position-absolute top-50 start-50 translate-middle text-center">
                        <div class="spinner-border text-info" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-1 text-muted">Đang tải camera...</p>
                    </div>
                </div>
                <div id="scanner-error" class="text-danger fw-bolder p-3" style="display: none"></div>
                <div id="loadingMessage" hidden="">⌛ Loading video...</div>
                <div>
                    <div class="card border-0">
                        <div class="card-body p-2">
                            <div class="col overflow-auto" id="table_wrapper" >
                                <div class="card border-0 position-relative h-100">
                                    <div class="card-header border border-bottom-0">
                                        <h5 class="card-title fw-bolder">Thông tin lô cần xuất</h5>
                                    </div>
                                    <div class="card-body p-0 d-flex flex-column">
                                        <table class="table table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">STT</th>
                                                    <th scope="col">Mã lô</th>
                                                    <th scope="col">Sẩn phẩm</th>
                                                    <th scope="col" style="max-width: 50px" class="text-break">SL</th>
                                                    <th scope="col">Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody id="table_list_mobile"></tbody>
                                        </table>
                                        {{-- <div id="empty_scanned_message"
                                            class="flex-grow-1 border border-top-0 d-flex align-items-center justify-content-center">
                                            <p>Chữa có mã QR được quét!</p>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>