@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
    <script>
        'use strict';

        window.trans = window.trans || {};

        window.trans.order = {{ Js::from(trans('plugins/showroom::showroom')) }};
    </script>
@endpush
{{-- @dd($showroom) --}}
@section('content')
    <create-exchange-goods
    :showrooms="{{ Auth::user()->showroom }}"
    :qrs_pay="{{ json_encode($list_pay) }}"
    :qrs_exchange="{{ json_encode($list_exchange) }}"
    :current_showroom="{{ $showroom }}"
    :showroom_selected="{{ $showroom->id }}"
    :is_create="false"
    ></create-exchange-goods>

    @include('plugins/qr-scan::scan-info')
@endsection
