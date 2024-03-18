@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @include('plugins/admin-handee-retail::modal.status-detail')
    @include('core/table::base-table')
@endsection
