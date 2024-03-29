<div class="col-lg-9 order-2 order-lg-1">
    <div class="row">
        <div class="col-md-8 mb-2">
            <div class="rp-card rp-card-sale-report">
                <div class="rp-card-header">
                    <h5>{{ trans('plugins/agent::reports.sales_reports') }}</h5>
                </div>

                <div class="rp-card__content">
                    <div id='sales-report-chart'>
                        <sales-reports-chart
                            url="{{ route('marketplace.vendor.chart.month') }}"
                            date_from='{{ $count['startDate']->toDateString() }}'
                            date_to='{{ $count['endDate']->toDateString() }}'
                        ></sales-reports-chart>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rp-card rp-card-earning">
                <div class="rp-card-header">
                    <h5>{{ trans('plugins/agent::reports.earnings') }}</h5>
                </div>
                <div class="rp-card-content">
                    @if (collect($count['revenues'])->count())
                        <div class="rp-card-chart position-relative">
                            <revenue-chart
                                :data="{{ json_encode(collect($count['revenues'])->map(function ($value) {return Arr::only($value, ['label', 'value', 'color']);})) }}"
                            ></revenue-chart>
                            <div class="rp-card-information">
                                <i class="fas fa-wallet"></i>
                                @foreach (collect($count['revenues'])->where('status') as $item)
                                    <strong>{{ format_price($item['value']) }}</strong>
                                @endforeach
                                <small>{{ trans('plugins/agent::reports.total_earnings') }}</small>
                            </div>
                        </div>
                        <div class="rp-card-status">
                            @foreach ($count['revenues'] as $item)
                                <p>
                                    <small>
                                        <small><i
                                                class="fas fa-circle me-2"
                                                style="color: {{ Arr::get($item, 'color') }}"
                                            ></i></small>
                                    </small>
                                    <strong>{{ format_price($item['value']) }}</strong>
                                    <span>{{ $item['label'] }}</span>
                                </p>
                            @endforeach
                        </div>
                    @else
                        <div>
                            @include('core/dashboard::partials.no-data')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 order-1 order-lg-2 mb-3">
    <section class="rp-card-report-statics">
        <div class="rp-card-header">
            <h5 class="font-weight-bold">{{ trans('plugins/agent::reports.statistics') }}</h5>
        </div>
        <div class="rp-card-content row row-cols-md-2 row-cols-lg-1 row-cols-1">
            <div class="col my-2">
                <div class="d-flex rounded px-2 py-3 h-100 position-relative bg-yellow-opacity">
                    <div class="block-left d-flex me-1">
                        <span class="align-self-center bg-white p-1"><i
                                class="fas fa-shopping-bag fa-2x m-2"></i></span>
                    </div>
                    <div class="block-content mx-3">
                        <p class="mb-1">{{ trans('plugins/agent::reports.orders') }}</p>
                        <h5>{{ $count['orders'] }}</h5>
                    </div>
                    @if (Auth::user()->hasPermission('orders.index'))
                        <a
                            class="stretched-link"
                            href="{{ route('orders.index') }}"
                        ></a>
                    @endif
                </div>
            </div>
            <div class="col my-2">
                <div class="d-flex rounded px-2 py-3 h-100 bg-blue-opacity">
                    <div class="block-left d-flex me-1">
                        <span class="align-self-center bg-white p-1"><i
                                class="fas fa-hand-holding-usd fa-2x m-2"></i></span>
                    </div>
                    <div class="block-content mx-3">
                        <p class="mb-1">{{ trans('plugins/agent::reports.revenue') }}</p>
                        <h5>{{ format_price(collect($count['revenues'])->where('status')->sum('value')) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col my-2">
                <div class=" d-flex rounded px-2 py-3 h-100 position-relative bg-green-opacity">
                    <div class="block-left d-flex me-1">
                        <span class="align-self-center bg-white p-1"><i class="fas fa-database fa-2x m-2"></i></span>
                    </div>
                    <div class="block-content mx-3">
                        <p class="mb-1">{{ trans('plugins/agent::reports.products') }}</p>
                        <h5>{{ $count['products'] }}</h5>
                    </div>
                    @if (Auth::user()->hasPermission('products.index'))
                        <a
                            class="stretched-link"
                            href="{{ route('products.index') }}"
                        ></a>
                    @endif
                </div>
            </div>
            <div class="col my-2">
                <div class="d-flex rounded px-2 py-3 h-100 position-relative bg-red-pink-opacity">
                    <div class="block-left d-flex me-1">
                        <span class="align-self-center bg-white p-1"><i class="fas fa-users fa-2x m-2"></i></span>
                    </div>
                    <div class="block-content mx-3">
                        <p class="mb-1">{{ trans('plugins/agent::reports.customers') }}</p>
                        <h5>{{ $count['customers'] }}</h5>
                    </div>
                    @if (Auth::user()->hasPermission('customers.index'))
                        <a
                            class="stretched-link"
                            href="{{ route('customers.index') }}"
                        ></a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
