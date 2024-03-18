@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @include('plugins/agent::modal.scan-mobile')
    {{-- @include('plugins/qr-scan::scan-product-sell') --}}


    @php
        $checkDisabled = $nameAgent == null ? false : true;
    @endphp
    <div
        id="data-warehouse"
        class="hidden"
        data-bs-target="{{route('agent.orders.check-product-order-by-agent')}}"
        data-bs-toggle="{{route('agent.orders.submit-payment')}}"
    ></div>

    <div class=" d-flex d-md-none gap-3 align-items-center d-none">
        @if(!$checkDisabled)
        <div style="width:200px">
            <x-core::form.select
                name="select-order-agent"
                :outlined="true"
                class="select-agent d-none"
                :options="$agentList"
                :value="$agent_id"
                data-bb-toggle="collapse"
                data-bb-target=".email-fields"
                :searchable="true"
            />
        </div>
    @else
        <span
            class="select2 select2-container select2-container--default select2-container--below select2-container--focus"
            dir="ltr" data-select2-id="select2-data-1-x4a0" style="width: 200px;"><span class="selection"><span
                    class="select2-selection select2-selection--single disabled" role="combobox" aria-haspopup="true"
                    aria-expanded="false" tabindex="0" aria-disabled="false"
                    aria-labelledby="select2-select-order-agent-container"
                    aria-controls="select2-select-order-agent-container">
                    <span class="select2-selection__rendered"
                          id="select2-select-order-agent-container"
                          role="textbox" aria-readonly="true"
                          title="Đại lý Quận 9">{{$nameAgent}}</span><span
                        class="select2-selection__arrow" role="presentation"><b
                            role="presentation"></b></span></span></span><span class="dropdown-wrapper"
                                                                               aria-hidden="true"></span></span>
    @endif
        <button 
          id="open_scan_modal"
          class="btn btn-primary mb-3"
          data-bs-toggle="modal"
          data-bs-target="#agent_order_scan_mobile_modal"
        >
        Tạo đơn hàng
       </button>
    </div>
@stop
