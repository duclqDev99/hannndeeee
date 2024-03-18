@php
    $status = setting('shipping_viettel_post_status', 0);
    $testKey = setting('shipping_viettel_post_test_key') ?: '';
    $prodKey = setting('shipping_viettel_post_production_key') ?: '';
    $test = setting('shipping_viettel_post_sandbox', 1) ?: 0;
    $logging = setting('shipping_viettel_post_logging', 1) ?: 0;
    $cacheResponse = setting('shipping_viettel_post_cache_response', 1) ?: 0;
    $webhook = setting('shipping_viettel_post_webhooks', 1) ?: 0;
@endphp

<x-core::card>
    <x-core::table :striped="false" :hover="false">
        <x-core::table.body>
            <x-core::table.body.cell class="border-end" style="width: 5%">
                <x-core::icon name="ti ti-truck-delivery" />
            </x-core::table.body.cell>
            <x-core::table.body.cell style="width: 20%">
                <img
                    class="filter-black"
                    src="{{ url('vendor/core/plugins/viettel-post/images/logo-dark.svg') }}"
                    alt="Viettel Post"
                >
            </x-core::table.body.cell>
            <x-core::table.body.cell>
                <a href="https://partner.viettelpost.vn" target="_blank" class="fw-semibold">Viettel Post</a>
                <p class="mb-0">{{ trans('plugins/viettel-post::viettel-post.description') }}</p>
            </x-core::table.body.cell>
            <x-core::table.body.row class="bg-white">
                <x-core::table.body.cell colspan="3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div @class(['payment-name-label-group', 'd-none' => ! $status])>
                                <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                                <label class="ws-nm inline-display method-name-label">Viettel Post</label>
                            </div>
                        </div>

                        <x-core::button
                            data-bs-toggle="collapse"
                            href="#collapse-shipping-method-viettel-post"
                            aria-expanded="false"
                            aria-controls="collapse-shipping-method-viettel-post"
                        >
                            @if ($status)
                                {{ trans('core/base::layouts.settings') }}
                            @else
                                {{ trans('core/base::forms.edit') }}
                            @endif
                        </x-core::button>
                    </div>
                </x-core::table.body.cell>
            </x-core::table.body.row>
            <x-core::table.body.row class="collapse" id="collapse-shipping-method-viettel-post">
                <x-core::table.body.cell class="border-left" colspan="3"> 
                    <x-core::form :url="route('ecommerce.shipments.viettel-post.settings.update')">
                        <x-core::form-group>
                            <x-core::form.toggle
                                name="shipping_viettel_post_status"
                                :checked="setting('shipping_viettel_post_status')"
                                :label="trans('plugins/shippo::shippo.activate')"
                            />
                        </x-core::form-group>
                        <div class="row">
                        <x-core::form.text-input
                            name="shipping_viettel_post_username"
                            label="UserName"
                            placeholder="Username"
                            :value="setting('shipping_viettel_post_username') ?: ''"
                        />

                        <x-core::form.text-input
                            name="shipping_viettel_post_password"
                            label="Password"
                            placeholder="Password"
                            :value="BaseHelper::hasDemoModeEnabled() ? Str::mask($prodKey, '*', 10) : $prodKey"
                            :value="setting('shipping_viettel_post_password') ?: ''"
                            type="password"
                        />
                        </div>
                        @env('demo')
                                    <x-core::alert type="danger">
                                        {{ trans('plugins/shippo::shippo.disabled_in_demo_mode') }}
                                    </x-core::alert>
                                @else
                                    <div class="text-end">
                                        <x-core::button type="submit" color="primary">
                                            {{ trans('core/base::forms.update') }}
                                        </x-core::button>
                                    </div>
                        @endenv
                    </x-core::form>
                </x-core::table.body.cell>
            </x-core::table.body.row>
        </x-core::table.body>
    </x-core::table>
</x-core::card>
