@if (app(Botble\ViettelPost\ViettelPost::class)->canCreateTransaction($shipment))
    @php
        $url = route('ecommerce.shipments.viettel-post.show', $shipment->id);
        if (!is_in_admin(true) && is_plugin_active('marketplace')) {
            $url = route('marketplace.vendor.orders.viettel-post.show', $shipment->id);
        }
    @endphp
    <button
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#viettel-post-view-n-create-transaction"
        data-url="{{ $url }}"
        type="button"
    >
        <img
            src="{{ url('vendor/core/plugins/viettel-post/images/icon.svg') }}"
            alt="viettel-post"
            height="16"
        >
        <span>{{ trans('plugins/viettel-post::viettel-post.transaction.view_and_create') }}</span>
    </button>

    <div
        class="modal fade"
        id="viettel-post-view-n-create-transaction"
        aria-labelledby="viettel-post-view-n-create-transaction-label"
        aria-hidden="true"
        tabindex="-1"
    >
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5
                        class="modal-title"
                        id="viettel-post-view-n-create-transaction-label"
                    >{{ trans('plugins/viettel-post::viettel-post.transaction.view_and_create') }}</h5>
                    <button
                        class="btn-close"
                        data-bs-dismiss="modal"
                        type="button"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
@endif

@if ($shipment->label_url)
    <a
        class="btn btn-success"
        href="{{ $shipment->label_url }}"
        target="_blank"
        rel="noopener noreferrer"
    >
        <i class="fa fa-print"></i>
        <span>{{ trans('plugins/viettel-post::viettel-post.print_label') }}</span>
    </a>
@endif
