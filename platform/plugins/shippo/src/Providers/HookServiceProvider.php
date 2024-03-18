<?php

namespace Botble\Shippo\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Shipment;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Shippo\Shippo;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter('handle_shipping_fee', [$this, 'handleShippingFee'], 11, 2);

        add_filter(SHIPPING_METHODS_SETTINGS_PAGE, [$this, 'addSettings'], 2);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == ShippingMethodEnum::class) {
                $values['SHIPPO'] = SHIPPO_SHIPPING_METHOD_NAME;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == ShippingMethodEnum::class && $value == SHIPPO_SHIPPING_METHOD_NAME) {
                return 'Shippo';
            }

            return $value;
        }, 2, 2);

        // add_filter('shipment_buttons_detail_order', function (?string $content, Shipment $shipment) {
        //     Assets::addScriptsDirectly('vendor/core/plugins/shippo/js/shippo.js');

        //     return $content . view('plugins/shippo::buttons', compact('shipment'))->render();
        // }, 1, 2);
    }

    public function handleShippingFee(array $result, array $data): array
    {
        if (! $this->app->runningInConsole() && setting('shipping_shippo_status') == 1) {
            // Arr::forget($data, 'extra.COD');
            // $results = app(Shippo::class)->getRates($data);
            // if (Arr::get($data, 'payment_method') == PaymentMethodEnum::COD) {
            //     $rates = Arr::get($results, 'shipment.rates') ?: [];
            //     foreach ($rates as &$rate) {
            //         $rate['disabled'] = true;
            //         $rate['error_message'] = __('Not available in COD payment option.');
            //     }

            //     Arr::set($results, 'shipment.rates', $rates);
            // }

            // $result['shippo'] = Arr::get($results, 'shipment.rates') ?: [];
            $result['shippo']['method_name'] = "shippo";
            $result['shippo']['title'] = "Shippo";
            $result['shippo']['image'] = "data:image/png;base64,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";
            $result['shippo']['get_rates_url'] = route('ecommerce.shipments.shippo.rates');
        }

        return $result;
    }

    public function addSettings(string|null $settings): string
    {
        $logFiles = [];

        if (setting('shipping_shippo_logging')) {
            foreach (BaseHelper::scanFolder(storage_path('logs')) as $file) {
                if (Str::startsWith($file, 'shippo-')) {
                    $logFiles[] = $file;
                }
            }
        }

        return $settings . view('plugins/shippo::settings', compact('logFiles'))->render();
    }
}
