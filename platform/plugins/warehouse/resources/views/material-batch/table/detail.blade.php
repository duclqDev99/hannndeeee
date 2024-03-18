@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @include('plugins/warehouse::material-batch/modal/qr-scan-pc')
    @include('plugins/warehouse::material-batch/modal/qr-scan')

    <div class="mb-3 d-flex d-md-none justify-content-end">
        <button id="open_scan_modal" class="btn btn-primary">
            <span class="me-3">
                <i class="fa-solid fa-qrcode"></i>
            </span>
            Quét QR
        </button>
    </div>
    @include('core/table::base-table')
@endsection
