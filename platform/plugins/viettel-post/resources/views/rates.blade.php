{!! Form::open([
    'url' => route('ecommerce.shipments.viettel-post.update-rate', $shipment->id),
    'class' => 'update-rate-shipment',
]) !!}
<div class="payment-checkout-form mt-3">
    @if ($rate)
        <div class="list-group list_payment_method">
            @include('plugins/viettel-post::rate', [
                'index' => 'selected',
                'item' => $rate,
                'attributes' => [
                    'checked' => true,
                ],
            ])
        </div>
    @else
        <div>
            <p>{{ trans('plugins/viettel-post::viettel-post.carrier_could_not_be_found') }}</p>
        </div>
    @endif
    <div
        class="accordion mt-3 @if ($rate) opacity-75 @endif"
        id="accordion-rates"
    >
        <div class="accordion-item">
            <h2
                class="accordion-header"
                id="heading-new-rates"
            >
                <button
                    class="accordion-button collapsed"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse-new-rates"
                    type="button"
                    aria-expanded="false"
                    aria-controls="collapse-new-rates"
                >
                    {{ trans('plugins/viettel-post::viettel-post.view_other_exchange_rates', ['count' => count($rates)]) }}
                </button>
            </h2>
            <div
                class="accordion-collapse collapse"
                id="collapse-new-rates"
                data-bs-parent="#accordion-rates"
                aria-labelledby="heading-new-rates"
            >
                <div class="accordion-body">
                    <div class="list-group list_payment_method">
                        @foreach ($rates as $item)
                            @include('plugins/viettel-post::rate', [
                                'index' => $loop->index,
                                'attributes' => [
                                    'disabled' => $rate ? 'disabled' : false,
                                ],
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<button
    class="btn btn-primary mt-2"
    type="submit"
>{{ trans('plugins/viettel-post::viettel-post.update_rate') }}</button>
{!! Form::close() !!}
