@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div id="main-order-content">
        @include('plugins/sales::orders.partials.canceled-alert', compact('order'))

        <div class="row row-cards">
            <div class="col-md-9">
                <x-core::card class="mb-3">
                    <x-core::card.header class="justify-content-between">
                        <x-core::card.title>
                            {{ trans('plugins/sales::orders.order_information') }} {{ $order->code }}
                        </x-core::card.title>

                        @if ($order->status != 'pending')
                            <x-core::badge color="info" class="d-flex align-items-center gap-1">
                                <x-core::icon name="ti ti-shopping-cart-check"></x-core::icon>
                                {{ trans('plugins/sales::orders.statuses.'. $order->status) }}
                            </x-core::badge>
                        @else
                            <x-core::badge color="warning" class="d-flex align-items-center gap-1">
                                <x-core::icon name="ti ti-shopping-cart"></x-core::icon>
                                {{ trans('plugins/sales::orders.statuses.'. $order->status) }}
                            </x-core::badge>
                        @endif
                    </x-core::card.header>

                    <x-core::table :hover="false" :striped="false">
                        <x-core::table.body>
                            @php
                            $totalPrice = 0;
                            @endphp
                            @foreach ($order->order_detail as $orderProduct)
                                <x-core::table.body.row>
                                    <x-core::table.body.cell style="width: 80px">
                                        <img
                                            src="{{ isset($orderProduct->product->product_image) ? RvMedia::getImageUrl($orderProduct->product->product_image, 'thumb', false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage() }}"
                                            alt="{{ $orderProduct->product_name }}"
                                        >
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell style="width: 45%">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <a
                                                href="javascript:void(0)"
                                                title="{{ $orderProduct->product_name }}"
                                                class="me-2"
                                            >
                                                {{ $orderProduct->product_name }}
                                            </a>
                                            <p class="mb-0">({{ trans('plugins/sales::orders.sku') }}: <strong>{{ $orderProduct->product->sku }}</strong>)</p>
                                        </div>

                                        @include(
                                            'plugins/ecommerce::themes.includes.cart-item-options-extras',
                                            ['options' => $orderProduct->product->options]
                                        )

                                        {!! apply_filters(ECOMMERCE_ORDER_DETAIL_EXTRA_HTML, null) !!}

                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        Giá thành: {{ format_price($orderProduct->product->price) }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>x</x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        Số lượng đặt:  {{ $orderProduct->quantity }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        Tổng: {{ format_price($orderProduct->quantity * $orderProduct->product->price) }}
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                                @php
                                $totalPrice += ($orderProduct->product->price * $orderProduct->quantity)
                                @endphp
                            @endforeach
                        </x-core::table.body>
                    </x-core::table>

                    <x-core::card.body>
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <x-core::table :hover="false" :striped="false" class="table-borderless text-end">
                                    <x-core::table.body>
                                        <x-core::table.body.row>
                                            <x-core::table.body.cell>{{ trans('plugins/sales::orders.quantity') }}</x-core::table.body.cell>
                                            <x-core::table.body.cell>
                                                {{ $order->order_detail->sum('quantity') }}
                                            </x-core::table.body.cell>
                                        </x-core::table.body.row>

                                        <x-core::table.body.row>
                                            <x-core::table.body.cell>
                                                {{ trans('plugins/sales::orders.sub_amount') }}</x-core::table.body.cell>
                                            <x-core::table.body.cell>
                                                {{ format_price($order->sub_total) }}
                                            </x-core::table.body.cell>
                                        </x-core::table.body.row>
                                        <x-core::table.body.row>
                                            <x-core::table.body.cell>
                                                {{ trans('plugins/sales::orders.discount') }}
                                                @if ($order->discount_description)
                                                    <p class="mb-0">{{ $order->discount_description }}</p>
                                                @endif
                                            </x-core::table.body.cell>
                                            <x-core::table.body.cell>
                                                {{ format_price($order->discount_amount) }}
                                            </x-core::table.body.cell>
                                        </x-core::table.body.row>
                                        @if (EcommerceHelper::isTaxEnabled())
                                            <x-core::table.body.row>
                                                <x-core::table.body.cell>
                                                    {{ trans('plugins/sales::orders.tax') }}
                                                </x-core::table.body.cell>
                                                <x-core::table.body.cell>
                                                    {{ format_price($order->tax_amount) }}
                                                </x-core::table.body.cell>
                                            </x-core::table.body.row>
                                        @endif

                                        {!! apply_filters('ecommerce_admin_order_extra_info', null, $order) !!}

                                        <x-core::table.body.row>
                                            <td colspan="2">
                                                <hr class="my-0">
                                            </td>
                                        </x-core::table.body.row>
                                    </x-core::table.body>
                                </x-core::table>

                                <div class="btn-list justify-content-end my-3">
                                    <x-core::button
                                        tag="a"
                                        href="{{ route('order-production.generate-invoice', $order) }}?type=print"
                                        target="_blank"
                                        icon="ti ti-printer"
                                    >
                                        {{ trans('plugins/sales::orders.print_invoice') }}
                                    </x-core::button>
                                    <x-core::button
                                        tag="a"
                                        :href="route('order-production.generate-invoice', $order->id)"
                                        target="_blank"
                                        icon="ti ti-download"
                                    >
                                        {{ trans('plugins/sales::orders.download_invoice') }}
                                    </x-core::button>
                                </div>

                                <form action="{{ route('order-production.edit', $order->id) }}">
                                    <x-core::form.textarea
                                        :label="trans('plugins/sales::orders.note')"
                                        name="description"
                                        :placeholder="trans('plugins/sales::orders.add_note')"
                                        :value="$order->description"
                                        class="textarea-auto-height"
                                    />

                                    <x-core::button type="button" class="btn-update-order">
                                        {{ trans('plugins/sales::orders.save') }}
                                    </x-core::button>
                                </form>
                            </div>
                        </div>
                    </x-core::card.body>

                    <div class="list-group list-group-flush">
                        @if ($order->status != Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED || $order->is_confirmed)
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <div class="text-uppercase">
                                    <x-core::icon name="ti ti-check" @class(['text-success' => $order->status]) />
                                    @if ($order->status == Botble\Sales\Enums\OrderStatusEnum::PENDING)
                                        {{ trans('plugins/sales::orders.order_was_confirmed') }}
                                    @else
                                        {{ trans('plugins/sales::orders.confirm_order') }}
                                    @endif
                                </div>
                                @if(Auth::user()->hasPermission('order-production.censorship'))
                                    @if ($order->status == Botble\Sales\Enums\OrderStatusEnum::PENDING)
                                        <div class="d-flex gap-2">
                                            <form action="{{ route('order-production.cancel', $order) }}">
                                                <x-core::button type="button" color="danger" class="btn-confirm-order">
                                                    {{ trans('plugins/sales::orders.send_cancel') }}
                                                </x-core::button>
                                            </form>
                                            <form action="{{ route('order-production.confirm', $order) }}">
                                                <input name="order_id" type="hidden" value="{{ $order->id }}">
                                                <x-core::button type="button" color="info" class="btn-confirm-order">
                                                    {{ trans('plugins/sales::orders.send_design') }}
                                                </x-core::button>
                                            </form>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </x-core::card>
            </div>

            <div class="col-md-3">
                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ trans('plugins/sales::orders.customer_label') }}
                        </x-core::card.title>
                        <div class="card-actions">
                            <button
                                type="button"
                                data-bs-toggle="tooltip"
                                data-placement="top"
                                title="Delete customer"
                                @click="removeCustomer()"
                                class="btn-action"
                            >
                                <span class="icon-tabler-wrapper icon-sm icon-left">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M18 6l-12 12" />
                                        <path d="M6 6l12 12" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </x-core::card.header>

                    <x-core::card.body>
                        @if(!empty($order->customer))
                        <div class="p-0">
                            <div class="mb-3">
                                <span class="avatar avatar-lg avatar-rounded" style="backgroundImage: url('{{ RvMedia::getImageUrl($order->customer->avatar, null, false, RvMedia::getDefaultImage()) }}')"></span>
                            </div>

                            <p class="mb-1 fw-semibold">{{ $order->customer->name }}</p>

                            <p class="mb-1">
                                <a href="mailto:{{ $order->customer->email }}">
                                    {{ $order->customer->email }}
                                </a>
                            </p>

                            <p class="mb-1">
                                <a href="tel:{{ $order->customer->phone }}">
                                    {{ $order->customer->phone }}
                                </a>
                            </p>
                        </div>
                        @endif
                    </x-core::card.body>

                    @if(Auth::user()->hasPermission('order-production.edit-order'))
                        @if($order->status == Botble\Sales\Enums\OrderStatusEnum::PENDING || $order->status == Botble\Sales\Enums\OrderStatusEnum::CANCELED)
                        <x-core::card.footer>
                            <div class="btn-list">
                                <x-core::button
                                    tag="a"
                                    :href="route('order-production.reorder', ['order_id' => $order->id])"
                                >
                                    {{ trans('plugins/sales::orders.update_order') }}
                                </x-core::button>
                            </div>
                        </x-core::card.footer>
                        @endif
                    @endif
                </x-core::card>

                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title">{{ trans('plugins/sales::orders.type_order') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="position-relative box-search-advance customer">
                            <select
                                id="type_order"
                                class="form-control"
                                disabled
                            >
                                <option>{{ trans('plugins/sales::orders.' . $order->type_order) }}</option>
                            </select>
                            @if($order->type_order != Botble\Sales\Enums\TypeOrderEnum::SAMPLE)
                            <div class="link_order mt-3">
                                <div class="form-group">
                                    <label for="link_order" class="mb-2"><strong>Đơn hàng liên kết:</strong></label>
                                    <select id="link_order" class="form-control" disabled>
                                        <option>{{ $order->orderLink->order_code }}</option>
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
