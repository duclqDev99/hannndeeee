<?php

namespace Botble\ViettelPost\Http\Middleware;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Closure;

class WebhookMiddleware
{
    public function handle($request, Closure $next)
    {
        if (setting('shipping_viettel_post_webhooks', 1) == 1 && ($token = $request->input('_token'))) {
            if (setting('shipping_viettel_post_sandbox', 1) == 1) {
                $apiToken = setting('shipping_viettel_post_test_key');
            } else {
                $apiToken = setting('shipping_viettel_post_production_key');
            }

            if ($apiToken && $apiToken == $token) {
                return $next($request);
            }
        }

        return (new BaseHttpResponse())->setError()->setMessage('Ops!');
    }
}
