@include('plugins/agent::modal.scan-mobile')
<div
id="data-warehouse"
class="hidden"
data-bs-target="{{route('agent.orders.check-product-order-by-agent')}}"
data-bs-toggle="{{route('agent.orders.submit-payment')}}"
></div>
<div class=" d-flex d-md-none gap-3 align-items-center">
    <button
      id="open_scan_modal"
      class="btn btn-primary "
      data-bs-toggle="modal"
      data-bs-target="#agent_order_scan_mobile_modal"
    >
    Tạo đơn hàng
   </button>
</div>


