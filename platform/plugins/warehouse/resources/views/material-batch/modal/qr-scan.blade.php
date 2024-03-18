<!-- Modal -->
<div class="modal fade" id="material_batch_qr_scan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen">
        <div class="modal-content">
            <div class="modal-body bg-white p-0 overflow-hidden position-relative">
                <div class="h-100 d-flex flex-column">
                    <div
                        class="flex-grow-1 position-relative w-100 bg-light d-flex align-items-center justify-content-center overflow-hidden">
                        <canvas id="canvas" class="w-100 h-100"></canvas>
                        <div id="camera-loading" class="position-absolute top-50 start-50 translate-middle text-center">
                            <div class="spinner-border text-info" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-1 text-muted">Đang tải camera...</p>
                        </div>
                    </div>
                    <div class="position-relative">
                        {{-- <button id="close_scan_content_btn" class="rounded-circle btn btn-primary position-absolute top-0 end-0" style="z-index: 50">
                            <i class="fa-solid fa-xmark"></i>
                        </button> --}}
                        <div id="scanner-error" class="text-danger fw-bolder p-3" style="display: none"></div>
                        <div id="loadingMessage" hidden="">⌛ Loading video...</div>
                        <div id="scanner_content" class="bg-white p-2" style="display: none"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
