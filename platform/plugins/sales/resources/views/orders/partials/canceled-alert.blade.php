@if ($order->status == Botble\Sales\Enums\OrderStatusEnum::CANCELED)
    <x-core::alert type="warning">
        <x-slot:title>
            {{ trans('plugins/sales::orders.order_canceled') }}
        </x-slot:title>

        {{ trans('plugins/sales::orders.order_was_canceled_at') }}
        <strong>{{ BaseHelper::formatDate($order->updated_at, 'H:i d/m/Y') }}</strong>.
    </x-core::alert>
@elseif ($order->status == Botble\Sales\Enums\OrderStatusEnum::PROCESSING)
    <x-core::alert type="info">
        <x-slot:title>
            {{ trans('plugins/sales::orders.order_processing') }}
        </x-slot:title>

        {{ trans('plugins/sales::orders.order_was_processed_at') }}
        <strong>{{ BaseHelper::formatDate($order->updated_at, 'H:i d/m/Y') }}</strong>.
    </x-core::alert>
@endif
