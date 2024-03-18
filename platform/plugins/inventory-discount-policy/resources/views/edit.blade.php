@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
    @include('plugins/ecommerce::discounts.partials.trans')

    {!! JsValidator::formRequest(Botble\Ecommerce\Http\Requests\DiscountRequest::class) !!}
@endpush

@section('content')
    <x-core::form  enctype="multipart/form-data">
        <discount
            currency="{{ get_application_currency()->symbol }}"
            :custom_apply="{{json_encode(CUSTOMER_CLASS_TYPE)}}"
            :discount="{{ $discount }}"
        ></discount>
    </x-core::form>
@endsection
