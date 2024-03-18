@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::form  :url="route('inventory-discount-policy.create')" enctype="multipart/form-data"
    method="post" >
        <discount
            currency="{{ get_application_currency()->symbol }}"
            :custom_apply="{{json_encode(CUSTOMER_CLASS_TYPE)}}"
        ></discount>
    </x-core::form>
@stop
@push('footer')
    {!! $jsValidation !!}
@endpush
