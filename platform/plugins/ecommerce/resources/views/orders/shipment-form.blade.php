<div class="p-3">
    <form action="{{ $url }}">
        <div class="row">
            {{-- <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-1">{{ trans('plugins/ecommerce::shipping.shipping_address') }}</h4>
                <a class="btn-trigger-update-shipping-address"
                    href="javascript:void(0)">{{ trans('plugins/ecommerce::shipping.edit') }}</a>
            </div> --}}
            <div class="col-md-6">
                <x-core::form-group>
                    <x-core::form.label>{{ trans('plugins/ecommerce::shipping.shipping_method') }}</x-core::form.label>
                    <div id="select-shipping-provider">
                        <div data-bs-toggle="dropdown" aria-expanded="false">
                            <input class="input-hidden-shipping-method" name="method" type="hidden"
                                value="{{ $order->shipping_method }}">
                            <input class="input-hidden-shipping-option" name="option" type="hidden"
                                value="{{ $order->shipping_option }}">
                            <input class="form-control input-show-shipping-method" type="text"
                                value="{{ $order->shipping_method_name }}" readonly
                                placeholder="{{ trans('plugins/ecommerce::shipping.select_shipping_method') }}">
                        </div>
                        <div class="dropdown-menu">
                            <x-core::table class="table-shipping-select-options">
                                <x-core::table.header>
                                    <x-core::table.header.cell>
                                        {{ trans('plugins/ecommerce::shipping.packages') }}
                                        <p class="text-warning mb-0">
                                            {{ trans('plugins/ecommerce::shipping.warehouse') }}
                                            {{ get_ecommerce_setting('store_city') }},
                                            {{ get_ecommerce_setting('store_state') }}
                                        </p>
                                    </x-core::table.header.cell>
                                    <x-core::table.header.cell>
                                        {{ trans('plugins/ecommerce::shipping.shipping_fee_cod') }}
                                        <p class="text-warning mb-0">
                                            {{ trans('plugins/ecommerce::shipping.fee') }}
                                        </p>
                                    </x-core::table.header.cell>
                                </x-core::table.header>
                            </x-core::table>
                        </div>
                    </div>
                </x-core::form-group>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-core::form.text-input :label="'Nơi lấy hàng'" name="showroom" :value="$pickShowroom->name" class=""
                    :readonly="true" />
            </div>
            <div class="col-md-6">

                <div class="shipment-address-box-1">
                    <label class="form-label">
                        Giao đến
                    </label>
                    {{ $order->address->viettel_full_address }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-core::form.text-input :label="trans('plugins/ecommerce::shipping.weight_unit', ['unit' => ecommerce_weight_unit()])" name="weight" :readonly="true" :value="$weight"
                    class="input-mask-number shipment-form-weight" />
            </div>

            @if (is_plugin_active('payment'))
                <div class="col-md-6">
                    <div class="h-100 d-flex align-items-center">
                        @if ($order->payment->payment_channel == Botble\Payment\Enums\PaymentMethodEnum::COD)
                            @if ($order->payment->status !== Botble\Payment\Enums\PaymentStatusEnum::COMPLETED)
                                <x-core::form.text-input :label="trans('plugins/ecommerce::shipping.cod_amount')" class="input-mask-number" name="cod_amount"
                                    :data-thousands-separator="EcommerceHelper::getThousandSeparatorForInputMask()" :data-decimal-separator="EcommerceHelper::getDecimalSeparatorForInputMask()" :value="format_price($order->amount, null, true)" :group-flat="true">
                                    <x-slot:prepend>
                                        <span class="input-group-text">{{ get_application_currency()->symbol }}</span>
                                    </x-slot:prepend>
                                </x-core::form.text-input>
                            @endif
                        @else
                            @if ($order->payment->status !== Botble\Payment\Enums\PaymentStatusEnum::COMPLETED)
                                <p class="mb-0">
                                    <span class="badge bg-success text-success-fg">
                                        Đã thanh toán
                                    </span>
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <x-core::form.textarea :label="trans('plugins/ecommerce::shipping.note')" name="note" class="textarea-auto-height" rows="1" />

        <div class="d-flex justify-content-end align-items-center mt-5">
            {{-- <x-core::form.checkbox :label="trans('plugins/ecommerce::shipping.send_confirmation_email_to_customer')" name="send_mail" :value="1" :checked="true"
                :inline="true" /> --}}

            <div class="btn-list">
                <x-core::button type="button" class="btn-close-shipment-panel">
                    {{ trans('plugins/ecommerce::shipping.cancel') }}
                </x-core::button>
                <x-core::button type="button" color="primary" class="btn-create-shipment">
                    {{ trans('plugins/ecommerce::shipping.finish') }}
                </x-core::button>
            </div>
        </div>
    </form>

</div>
