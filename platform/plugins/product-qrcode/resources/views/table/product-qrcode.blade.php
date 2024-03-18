@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @include('plugins/qr-scan::scan-info')
    @include('core/table::base-table')
@endsection
