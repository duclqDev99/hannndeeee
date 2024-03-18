@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')

    <div
        class="max-width-1200"
        id="main-order"
    >
        <create-order-sale
            :products="{{ json_encode($products) }}"
            :product_ids="{{ json_encode($productIds) }}"
            @if ($customer) :customer="{{ $customer }}" @endif
            :customer_id="{{ $order->id_user }}"
            :sub_amount="{{ $order->amount }}"
            :total_amount="{{ $order->sub_total }}"
            :discount_amount="{{ $order->discount_amount }}"
            @if ($order->coupon_code) :coupon_code="'{{ $order->coupon_code }}'" @endif
            @if ($order->discount_description) :discount_description="'{{ $order->discount_description }}'" @endif
            :currency="'{{ get_application_currency()->symbol }}'"
            :zip_code_enabled="{{ (int) EcommerceHelper::isZipCodeEnabled() }}"
            :use_location_data="{{ (int) EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation() }}"
            :sub_amount_label="'{{ format_price(0) }}'"
            :tax_amount_label="'{{ format_price(0) }}'"
            :promotion_amount_label="'{{ format_price(0) }}'"
            :discount_amount_label="'{{ format_price(0) }}'"
            :shipping_amount_label="'{{ format_price(0) }}'"
            :total_amount_label="'{{ format_price(0) }}'"
            :order = "{{ $order }}"
        ></create-order-sale>
    </div>
@stop

@push('header')
    <script>
        'use strict';

        window.trans = window.trans || {};

        window.trans.order = JSON.parse('{!! addslashes(json_encode(trans('plugins/sales::orders'))) !!}');
    </script>
@endpush
