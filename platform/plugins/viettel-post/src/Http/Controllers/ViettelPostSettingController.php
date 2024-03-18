<?php

namespace Botble\ViettelPost\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Setting\Supports\SettingStore;
use Botble\ViettelPost\ViettelPost;
use Botble\Support\Services\Cache\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ViettelPostSettingController extends BaseController
{
    public function update(Request $request, BaseHttpResponse $response, SettingStore $settingStore)
    {
        $data = Arr::where($request->except(['_token']), function ($value, $key) {
            return Str::startsWith($key, 'shipping_');
        });
        foreach ($data as $settingKey => $settingValue) {
            $settingStore->set($settingKey, $settingValue);
        }

        $settingStore->save();

        $cache = new Cache(app('cache'), HandleShippingFeeService::class);
        $cache->flush();

        $message = trans('plugins/viettel-post::viettel-post.saved_shipping_settings_success');
        $isError = false;

        if ($request->input('shipping_viettel_post_validate')) {
            $errors = app(ViettelPost::class)->validate();
            if ($errors) {
                $message = $errors[0];
                $isError = true;
            }
        }

        return $response->setError($isError)->setMessage($message);
    }
}
