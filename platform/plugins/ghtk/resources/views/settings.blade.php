@php
    $status = setting('shipping_ghtk_status', 0);
    $api_token_key = setting('shipping_ghtk_api_token_key') ?: '';

@endphp

<x-core::card>
    <x-core::table :striped="false" :hover="false">
        <x-core::table.body>
            <x-core::table.body.cell class="border-end" style="width: 5%">
                <x-core::icon name="ti ti-truck-delivery" />
            </x-core::table.body.cell>
            <x-core::table.body.cell style="width: 20%">
                <img class="filter-black" src="https://cdn.haitrieu.com/wp-content/uploads/2022/05/Logo-GHTK-H.png"
                    alt="ghtk">
            </x-core::table.body.cell>
            <x-core::table.body.cell>
                <a href="https://khachhang.giaohangtietkiem.vn" target="_blank" class="fw-semibold">GHTK</a>
                <p class="mb-0">{{ trans('plugins/ghtk::ghtk.description') }}</p>
            </x-core::table.body.cell>
            <x-core::table.body.row class="bg-white">
                <x-core::table.body.cell colspan="3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div @class(['payment-name-label-group', 'd-none' => !$status])>
                                <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                                <label class="ws-nm inline-display method-name-label">GHTK</label>
                            </div>
                        </div>

                        <div>
                            <x-core::button data-bs-toggle="modal" href="#setting-pick-address-modal" data-service-type="{{$serviceType}}">
                                Cấu hình địa chỉ lấy hàng
                            </x-core::button>
                            <x-core::button data-bs-toggle="collapse" href="#collapse-shipping-method-ghtk"
                                aria-expanded="false" aria-controls="collapse-shipping-method-ghtk">
                                @if ($status)
                                    {{ trans('core/base::layouts.settings') }}
                                @else
                                    {{ trans('core/base::forms.edit') }}
                                @endif
                            </x-core::button>
                        </div>
                    </div>
                </x-core::table.body.cell>
            </x-core::table.body.row>
            <x-core::table.body.row class="collapse show" id="collapse-shipping-method-ghtk">
                <x-core::table.body.cell class="border-left" colspan="3">
                    <x-core::form :url="route('ecommerce.shipments.ghtk.settings.update')">
                        <div class="row">
                            <div class="col-sm-12">
                                <x-core::form.text-input name="shipping_ghtk_api_token_key" :label="trans('plugins/ghtk::ghtk.live_api_token')"
                                    placeholder="<API-KEY>" :disabled="BaseHelper::hasDemoModeEnabled()" :value="BaseHelper::hasDemoModeEnabled()
                                        ? Str::mask($api_token_key, '*', 10)
                                        : $api_token_key" />

                                <x-core::form-group>
                                    <x-core::form.toggle name="shipping_ghtk_status" :checked="$status"
                                        :label="trans('plugins/ghtk::ghtk.activate')" />
                                </x-core::form-group>

                                {{-- <x-core::form-group>
                                    <x-core::form.toggle
                                        name="shipping_ghtk_logging"
                                        :checked="$logging"
                                        :label="trans('plugins/ghtk::ghtk.logging')"
                                    />
                                </x-core::form-group>

                                <x-core::form-group>
                                    <x-core::form.toggle
                                        name="shipping_ghtk_cache_response"
                                        :checked="$cacheResponse"
                                        :label="trans('plugins/ghtk::ghtk.cache_response')"
                                    />
                                </x-core::form-group> --}}

                                {{-- <x-core::form-group>
                                    <x-core::form.toggle
                                        name="shipping_ghtk_webhooks"
                                        :checked="$webhook"
                                        :label="trans('plugins/ghtk::ghtk.webhooks')"
                                    />

                                    <x-core::form.helper-text>
                                        <a
                                            class="text-warning fw-bold"
                                            href="https://goghtk.com/docs/webhooks"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <span>Webhooks</span>
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <div>URL: <i>{{ route('ghtk.webhooks', ['_token' => '__API_TOKEN__']) }}</i>
                                        </div>
                                    </x-core::form.helper-text>
                                </x-core::form-group> --}}

                                @if (count($logFiles))
                                    <div class="form-group mb-3">
                                        <p class="mb-0">{{ __('Log files') }}: </p>
                                        <ul>
                                            @foreach ($logFiles as $logFile)
                                                <li><a href="{{ route('ecommerce.shipments.ghtk.view-log', $logFile) }}"
                                                        target="_blank"><strong>- {{ $logFile }} <i
                                                                class="fa fa-external-link"></i></strong></a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @env('demo')
                                <x-core::alert type="danger">
                                    {{ trans('plugins/ghtk::ghtk.disabled_in_demo_mode') }}
                                </x-core::alert>
                            @else
                                <div class="text-end">
                                    <x-core::button type="submit" color="primary">
                                        {{ trans('core/base::forms.update') }}
                                    </x-core::button>
                                </div>
                                @endenv
                            </div>
                        </div>
                    </x-core::form>
                </x-core::table.body.cell>
            </x-core::table.body.row>
        </x-core::table.body>
    </x-core::table>
</x-core::card>
