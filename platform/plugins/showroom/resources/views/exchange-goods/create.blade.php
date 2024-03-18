@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
    <script>
        'use strict';

        window.trans = window.trans || {};

        window.trans.order = {{ Js::from(trans('plugins/showroom::showroom')) }};
    </script>
@endpush
@php 
    if(Auth::user()->isSuperUser())
    {
        $showrooms = new \Botble\Showroom\Models\Showroom;
        $showrooms = $showrooms->where(['status' => \Botble\Base\Enums\BaseStatusEnum::PUBLISHED])->get();
    }else{
        $showrooms = Auth::user()->showroom->where(['status' => \Botble\Base\Enums\BaseStatusEnum::PUBLISHED])->get();
    }
@endphp
@section('content')
    <create-exchange-goods
    :showrooms="{{ $showrooms ?? '' }}"
    ></create-exchange-goods>

    @include('plugins/qr-scan::scan-info')
@endsection
