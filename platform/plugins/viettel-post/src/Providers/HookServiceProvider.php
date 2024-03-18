<?php

namespace Botble\ViettelPost\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Shipment;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\SharedModule\Trait\ViettelPostLoginTrait;
use Botble\ViettelPost\ViettelPost;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class HookServiceProvider extends ServiceProvider
{
    use ViettelPostLoginTrait;
    protected $token = null;
    public function boot(): void
    {
        add_filter('handle_shipping_fee', [$this, 'handleShippingFee'], 11, 2);

        add_filter(SHIPPING_METHODS_SETTINGS_PAGE, [$this, 'addSettings'], 2);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == ShippingMethodEnum::class) {
                $values['VIETTELPOST'] = VIETTEL_POST_SHIPPING_METHOD_NAME;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == ShippingMethodEnum::class && $value == VIETTEL_POST_SHIPPING_METHOD_NAME) {
                return 'ViettelPost';
            }

            return $value;
        }, 2, 2);

        // add_filter('shipment_buttons_detail_order', function (?string $content, Shipment $shipment) {
        //     Assets::addScriptsDirectly('vendor/core/plugins/viettel-post/js/viettel-post.js');

        //     return $content . view('plugins/viettel-post::buttons', compact('shipment'))->render();
        // }, 1, 2);
    }

    public function handleShippingFee(array $result, array $data): array
    {
        if (! $this->app->runningInConsole() && setting('shipping_viettel_post_status') == 1) {
            // if (Cache::has("shipping_viettel_post_token")) {
            //     $token = Cache::get("shipping_viettel_post_token");
            // }
            // else{
            //     $urlLogin = 'https://partner.viettelpost.vn/v2/user/Login';
            //     $dataLogin = [
            //         'USERNAME' => setting('shipping_viettel_post_username'),
            //         'PASSWORD' => setting('shipping_viettel_post_password'),
            //     ];
            //     $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
            //     $response = $client->post($urlLogin, [
            //         'body' => json_encode($dataLogin)
            //     ]);
            //     $resData = json_decode((string)$response->getBody(), true);
            //     if($resData['status'] == 200 && $resData['error'] == false){
            //         $token = $resData['data']['token'];
            //         $expirationMilliseconds = $resData['data']['expired'];

            //         // Chuyển đổi mili giây sang giây
            //         $expirationSeconds = $expirationMilliseconds / 1000;
                    
            //         // Tạo đối tượng Carbon từ thời gian hết hạn
            //         $expirationTime = Carbon::createFromTimestamp($expirationSeconds);
                    
            //         // Tính thời gian còn lại cho đến thời điểm hết hạn
            //         $now = Carbon::now();
            //         $secondsUntilExpiration = $expirationTime->diffInSeconds($now);

            //         // Lưu token vào cache với thời gian hết hạn tương ứng
            //         Cache::put("shipping_viettel_post_token", $token,$secondsUntilExpiration);
            //     }
            // }
            // Arr::forget($data, 'extra.COD');
            // $results = app(ViettelPost::class)->getRates($data);
            // if (Arr::get($data, 'payment_method') == PaymentMethodEnum::COD) {
            //     $rates = Arr::get($results, 'shipment.rates') ?: [];
            //     foreach ($rates as &$rate) {
            //         $rate['disabled'] = true;
            //         $rate['error_message'] = __('Not available in COD payment option.');
            //     }

            //     Arr::set($results, 'shipment.rates', $rates);
            // }

            // $result['viettel-post'] = Arr::get($results, 'shipment.rates') ?: [];

            // $this->token = $this->getTokenVietelPost();
            // Arr::forget($data, 'extra.COD');
            // $results = app(ViettelPost::class)->getRates($data);
            // if (Arr::get($data, 'payment_method') == PaymentMethodEnum::COD) {
            //     $rates = Arr::get($results, 'shipment.rates') ?: [];
            //     foreach ($rates as &$rate) {
            //         $rate['disabled'] = true;
            //         $rate['error_message'] = __('Not available in COD payment option.');
            //     }

            //     Arr::set($results, 'shipment.rates', $rates);
            // }

            $result['viettel_post']['method_name'] = "viettel_post";
            $result['viettel_post']['title'] = "Viettel Post";
            $result['viettel_post']['image'] = "https://viettelpost.com.vn/wp-content/uploads/2020/03/logo-380x270.jpg";
            $result['viettel_post']['get_rates_url'] = route('ecommerce.shipments.viettel-post.rates');
            $result['viettel_post']['is_default'] = false;
            $result['viettel_post']['options'] = [];

        }
        return $result;
    }

    public function addSettings(string|null $settings): string
    {
        $logFiles = [];

        $serviceType = 'viettel_post';
        if (setting('shipping_viettel_post_logging')) {
            foreach (BaseHelper::scanFolder(storage_path('logs')) as $file) {
                if (Str::startsWith($file, 'viettel-post-')) {
                    $logFiles[] = $file;
                }
            }
        }

        return $settings . view('plugins/viettel-post::settings', compact('logFiles', 'serviceType'))->render();
    }
}
