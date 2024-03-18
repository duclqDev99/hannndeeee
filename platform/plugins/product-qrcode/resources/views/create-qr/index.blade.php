@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    <div
        class="max-width-1200"
        id="main-order"
    >
        <search-product-component
            :currency="'{{ get_application_currency()->symbol }}'"
            :zip_code_enabled="{{ (int) EcommerceHelper::isZipCodeEnabled() }}"
            :use_location_data="{{ (int) EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation() }}"
            :is_tax_enabled={{ (int) EcommerceHelper::isTaxEnabled() }}
            :sub_amount_label="'{{ format_price(0) }}'"
            :tax_amount_label="'{{ format_price(0) }}'"
            :promotion_amount_label="'{{ format_price(0) }}'"
            :discount_amount_label="'{{ format_price(0) }}'"
            :shipping_amount_label="'{{ format_price(0) }}'"
            :total_amount_label="'{{ format_price(0) }}'"
            :check_permission="'{{Auth::guard()->user()->hasPermission('product-qrcode.export-qrcodes')}}'"
        ></search-product-component>
    </div>
@stop

@push('header')
    <script>
        'use strict';

        window.trans = window.trans || {};

        window.trans.order = JSON.parse('{!! addslashes(json_encode(trans('plugins/ecommerce::order'))) !!}');

        window.LaravelRoutes = {
            createQrCodeRoute: "{{ route('product-qrcode.create-qrcodes') }}",
            indexQrCodeRoute: "{{ route('product-qrcode.index') }}",
            // thêm các route khác ở đây nếu cần
        };
    </script>
@endpush
