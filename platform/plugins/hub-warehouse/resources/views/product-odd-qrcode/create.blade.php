@extends(BaseHelper::getAdminMasterLayoutTemplate())
@push('header')
    <script>
        'use strict';

        window.trans = window.trans || {};

        window.trans.order = {{ Js::from(trans('plugins/agent::order')) }};
    </script>
@endpush

@section('content')
    <agent-create-product
        :currency="'{{ get_application_currency()->symbol }}'"
        :zip_code_enabled="{{ (int) EcommerceHelper::isZipCodeEnabled() }}"
        {{-- :use_location_data="{{ (int) EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation() }}" --}}
        use_location_data="true"
        :is_tax_enabled={{ (int) EcommerceHelper::isTaxEnabled() }}
        :sub_amount_label="'{{ format_price(0) }}'"
        :tax_amount_label="'{{ format_price(0) }}'"
        :promotion_amount_label="'{{ format_price(0) }}'"
        :discount_amount_label="'{{ format_price(0) }}'"
        :shipping_amount_label="'{{ format_price(0) }}'"
        :total_amount_label="'{{ format_price(0) }}'"
        :agency_product='{{$agencyProduct}}'
        :agent_warehouse='{{$warehouse}}'
    ></agent-create-product>
@endsection
