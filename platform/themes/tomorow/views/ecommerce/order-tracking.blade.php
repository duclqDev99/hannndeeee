<section class="section--blog">
    <div class="section__content">
        <section class="section--auth">
            <form class="form--auth tracking-form" method="GET" action="{{ route('public.orders.tracking') }}">
                <div class="form__header">
                    <h3>{{ __('Order tracking') }}</h3>
                    <p>{{ __('Tracking your order status') }}</p>
                </div>
                <div class="form__content">
                    <div class="form-group">
                        <label for="txt-order-id">{{ __('Order ID') }}<sup>*</sup></label>
                        <input class="form-control" name="order_id" id="txt-order-id" type="text" value="{{ old('order_id', request()->input('order_id')) }}" placeholder="{{ __('Order ID') }}">
                        @if ($errors->has('order_id'))
                            <span class="text-danger">{{ $errors->first('order_id') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="txt-email">{{ __('Email Address') }}<sup>*</sup></label>
                        <input class="form-control" name="email" id="txt-email" type="email" value="{{ old('email', request()->input('email')) }}" placeholder="{{ __('Please enter your email address') }}">
                        @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="form__actions">
                        <button type="submit" class="btn--custom btn--rounded btn--outline">{{ __('Find') }}</button>
                    </div>
                </div>
            </form>
            @if ($order)
                <div class="customer-order-detail">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('Order information') }}</h5>
                            <p>
                                <span>{{ __('Order number') }}:</span>
                                <strong>{{ $order->code }}</strong>
                            </p>
                            <p>
                                <span>{{ __('Time') }}:</span> <strong>{{ $order->created_at->translatedFormat('h:m M d, Y') }}</strong>
                            </p>
                            <p>
                                <span>{{ __('Order status') }}:</span> <strong class="text-info">{{ $order->status->label() }}</strong>
                            </p>

                            <p>
                                <span>{{ __('Payment method') }}:</span> <strong class="text-info">{{ $order->payment->payment_channel->label() }}</strong>
                            </p>

                            <p>
                                <span>{{ __('Payment status') }}:</span> <strong class="text-info">{{ $order->payment->status->label() }}</strong>
                            </p>
                            @if ($order->description)
                                <p>
                                    <span>{{ __('Note') }}:</span> <strong class="text-warning"><i>{{ $order->description }}</i></strong>
                                </p>
                            @endif

                        </div>
                        <div class="col-md-6 customer-information-box">
                            <h5>{{ __('Customer information') }}</h5>

                            <p>
                                <span>{{ __('Full Name') }}:</span> <strong>{{ $order->address->name }} </strong>
                            </p>

                            <p>
                                <span>{{ __('Phone') }}:</span> <strong>{{ $order->address->phone }} </strong>
                            </p>

                            <p>
                                <span>{{ __('Address') }}:</span> <strong> {{ $order->address->address }} </strong>
                            </p>

                            <p>
                                <span>{{ __('City') }}:</span> <strong>{{ $order->address->city_name }} </strong>
                            </p>
                            <p>
                                <span>{{ __('State') }}:</span> <strong> {{ $order->address->state_name }} </strong>
                            </p>
                            @if (EcommerceHelper::isUsingInMultipleCountries())
                                <p>
                                    <span>{{ __('Country') }}:</span> <strong> {{ $order->address->country_name }} </strong>
                                </p>
                            @endif
                            @if (EcommerceHelper::isZipCodeEnabled())
                                <p>
                                    <span>{{ __('Zip code') }}:</span> <strong> {{ $order->address->zip_code }} </strong>
                                </p>
                            @endif
                        </div>
                    </div>
                    <br>
                    <h5>{{ __('Order detail') }}</h5>
                    <div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th style="width: 100px">{{ __('Quantity') }}</th>
                                    <th class="text-right price">{{ __('Total') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($order->products as $key => $orderProduct)
                                    @php
                                        $product = get_products([
                                            'condition' => [
                                                'ec_products.id' => $orderProduct->product_id,
                                            ],
                                            'take' => 1,
                                            'select' => [
                                                'ec_products.id',
                                                'ec_products.images',
                                                'ec_products.name',
                                                'ec_products.price',
                                                'ec_products.sale_price',
                                                'ec_products.sale_type',
                                                'ec_products.start_date',
                                                'ec_products.end_date',
                                                'ec_products.sku',
                                                'ec_products.is_variation',
                                                'ec_products.status',
                                                'ec_products.order',
                                                'ec_products.created_at',
                                            ],
                                        ]);
                                    @endphp
                                    @if ($product)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <img src="{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}" width="50" alt="{{ $product->nameHomepage }}" loading="lazy"/></td>
                                            <td>
                                                {!! BaseHelper::clean($product->nameHomepage) !!} @if ($product->sku) ({{ $product->sku }}) @endif
                                                @if ($product->is_variation)
                                                    <p>
                                                        <small>{{ $product->variation_attributes }}</small>
                                                    </p>
                                                @endif

                                                @if (!empty($orderProduct->options) && is_array($orderProduct->options))
                                                    @foreach($orderProduct->options as $option)
                                                        @if (!empty($option['key']) && !empty($option['value']))
                                                            <p class="mb-0"><small>{{ $option['key'] }}: <strong> {{ $option['value'] }}</strong></small></p>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ format_price($orderProduct->price) }}</td>
                                            <td>{{ $orderProduct->qty }}</td>
                                            <td class="text-right money">
                                                <strong>
                                                    {{ format_price($orderProduct->price * $orderProduct->qty)}}
                                                </strong>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p>
                            <span>{{ __('Shipping fee') }}:</span> <strong>  {{ format_price($order->shipping_amount) }} </strong>
                        </p>

                        @if (EcommerceHelper::isTaxEnabled())
                            <p>
                                <span>{{ __('Tax') }}:</span> <strong> {{ format_price($order->tax_amount) }} </strong>
                            </p>
                        @endif

                        <p>
                            <span>{{ __('Discount') }}: </span> <strong> {{ format_price($order->discount_amount) }}</strong>
                            @if ($order->discount_amount)
                                @if ($order->coupon_code)
                                    ({!! __('Coupon code: ":code"', ['code' => Html::tag('strong', $order->coupon_code)->toHtml()]) !!})
                                @elseif ($order->discount_description)
                                    ({{ $order->discount_description }})
                                @endif
                            @endif
                        </p>

                        <p>
                            <span>{{ __('Total Amount') }}:</span> <strong> {{ format_price($order->amount) }} </strong>
                        </p>
                    </div>

                    @if ($order->shipment->id)
                        <br>
                        <h5>{{ __('Shipping Information:') }}</h5>
                        <p><span class="d-inline-block">{{ __('Shipping Status') }}</span>: <strong class="d-inline-block text-info">{!! BaseHelper::clean($order->shipment->status->toHtml()) !!}</strong></p>
                        @if ($order->shipment->shipping_company_name)
                            <p><span class="d-inline-block">{{ __('Shipping Company Name') }}</span>: <strong class="d-inline-block">{{ $order->shipment->shipping_company_name }}</strong></p>
                        @endif
                        @if ($order->shipment->tracking_id)
                            <p><span class="d-inline-block">{{ __('Tracking ID') }}</span>: <strong class="d-inline-block">{{ $order->shipment->tracking_id }}</strong></p>
                        @endif
                        @if ($order->shipment->tracking_link)
                            <p><span class="d-inline-block">{{ __('Tracking Link') }}</span>: <strong class="d-inline-block"><a
                                        href="{{ $order->shipment->tracking_link }}" target="_blank">{{ $order->shipment->tracking_link }}</a></strong></p>
                        @endif
                        @if ($order->shipment->note)
                            <p><span class="d-inline-block">{{ __('Delivery Notes') }}</span>: <strong class="d-inline-block">{{ $order->shipment->note }}</strong></p>
                        @endif
                        @if ($order->shipment->estimate_date_shipped)
                            <p><span class="d-inline-block">{{ __('Estimate Date Shipped') }}</span>: <strong class="d-inline-block">{{ $order->shipment->estimate_date_shipped }}</strong></p>
                        @endif
                        @if ($order->shipment->date_shipped)
                            <p><span class="d-inline-block">{{ __('Date Shipped') }}</span>: <strong class="d-inline-block">{{ $order->shipment->date_shipped }}</strong></p>
                        @endif
                    @endif
            @elseif (request()->input('order_id') || request()->input('email'))
                <p class="text-center text-danger">{{ __('Order not found!') }}</p>
            @endif
        </section>
    </div>
</section>
