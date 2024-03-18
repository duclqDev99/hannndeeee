@if ($materialBatch)
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-bolder">Thông tin lô #{{ $materialBatch->batch_code }}</h5>
            <div class="mb-2 d-flex align-items-center justify-content-between ">
                <p class="card-text mb-0">Sản phẩm</p>
                <p class="card-text fw-bolder">{{ $materialBatch->material->name }}</p>
            </div>
            <div class="mb-2 d-flex align-items-center justify-content-between ">
                <p class="card-text mb-0">Mã sản phẩm</p>
                <p class="card-text fw-bolder">{{ $materialBatch->material_code }}</p>
            </div>
            <div class="mb-2 d-flex align-items-center justify-content-between">
                <p class="card-text mb-0">Ngày nhập</p>
                <p class="card-text fw-bolder">{{ $materialBatch->created_at->format('d-m-Y') }}</p>
            </div>
            <div class="mb-2 d-flex align-items-center justify-content-between">
                <p class="card-text mb-0">Người nhập</p>
                <p class="card-text fw-bolder">{{ $materialBatch->receipt->invoice_confirm_name }}</p>
            </div>
            <div class="mb-2 d-flex align-items-center justify-content-between">
                <p class="card-text mb-0">SL nhập</p>
                <p class="card-text fw-bolder">{{ $materialBatch->start_qty }}</p>
            </div>
            <div class="mb-2 d-flex align-items-center justify-content-between">
                <p class="card-text mb-0">Còn lại</p>
                <p class="card-text fw-bolder">{{ $materialBatch->quantity }}</p>
            </div>
        </div>
    </div>
@else
   <span class="text-danger">Mã QR không hợp lệ</span>
@endif
